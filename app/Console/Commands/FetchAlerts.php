<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Webklex\IMAP\Facades\Client;
use App\Models\Alert;
use App\Models\AlertHistory;
use App\Models\Client as AlertClient;
use App\Models\ClassificationRule;
use App\Models\OAuthToken;
use App\Models\Setting;
use Illuminate\Support\Str;
use League\OAuth2\Client\Provider\Google;
use Carbon\Carbon;

class FetchAlerts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'alerts:fetch {--daemon : Run in a continuous loop}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch emails from IMAP and convert them to alerts';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if ($this->option('daemon')) {
            $this->info("Starting Alert Fetcher in Daemon mode...");
            while (true) {
                try {
                    $this->fetchData();
                } catch (\Exception $e) {
                    $this->error("Daemon encountered an error: " . $e->getMessage());
                    sleep(30); // Wait before retry
                }
                $this->info("Waiting 60 seconds before next fetch...");
                sleep(60);
            }
        }

        return $this->fetchData();
    }

    private function fetchData()
    {
        $email = env('IMAP_USERNAME');
        
        $this->info('Fetching alerts for: ' . $email);

        // Get OAuth token from database
        $oauthToken = OAuthToken::getForEmail($email);

        if (!$oauthToken) {
            $this->error("No OAuth token found for {$email}.");
            $this->warn("Please authorize the app by visiting: " . route('oauth.google'));
            return 1;
        }

        // Check if token is expired and refresh if needed
        if ($oauthToken->isExpired()) {
            $this->info('Access token expired. Refreshing...');
            $oauthToken = $this->refreshToken($oauthToken);
            
            if (!$oauthToken) {
                $this->error('Failed to refresh token. Please re-authorize.');
                return 1;
            }
        }

        $this->info('Connecting to IMAP server with OAuth2...');

        try {
            // Create IMAP client with OAuth2
            $client = Client::make([
                'host' => env('IMAP_HOST'),
                'port' => env('IMAP_PORT'),
                'encryption' => env('IMAP_ENCRYPTION'),
                'validate_cert' => true,
                'username' => $email,
                'password' => $oauthToken->access_token,
                'authentication' => 'oauth',
            ]);

            $client->connect();

            $folder = $client->getFolder('INBOX');
            $messages = $folder->query()->unseen()->get();

            $this->info('Found ' . $messages->count() . ' unread messages.');

            foreach ($messages as $message) {
                $subject = $message->getSubject();
                $body = $message->hasTextBody() ? $message->getTextBody() : $message->getHTMLBody();
                $from = $message->getFrom()[0]->mail;
                
                $this->info("Processing: $subject from $from");

                // Classification Logic: Identify Client
                $clientId = null;
                $allClients = AlertClient::whereNotNull('identifier_keywords')
                    ->orWhereNotNull('email_domain')
                    ->get();

                // 1. Try Keyword-based identification (High priority)
                foreach ($allClients as $c) {
                    if ($c->identifier_keywords) {
                        $keywords = array_map('trim', explode(',', $c->identifier_keywords));
                        foreach ($keywords as $keyword) {
                            if (empty($keyword)) continue;
                            if (stripos($subject, $keyword) !== false || stripos($body, $keyword) !== false) {
                                $clientId = $c->id;
                                break 2;
                            }
                        }
                    }
                }

                // 2. Try Email Domain-based identification (Medium priority)
                if (!$clientId) {
                    $domain = substr(strrchr($from, "@"), 1);
                    $clientByDomain = AlertClient::where('email_domain', $domain)->first();
                    if ($clientByDomain) {
                        $clientId = $clientByDomain->id;
                    }
                }

                // 3. Fallback to Unknown Client
                if (!$clientId) {
                    $unknownClient = AlertClient::firstOrCreate(
                        ['name' => 'Unknown Client'],
                        ['email_domain' => 'unknown.com']
                    );
                    $clientId = $unknownClient->id;
                }

                // Classification Logic: Identify Severity (Rules)
                $severity = 'default';
                $rules = ClassificationRule::orderBy('priority')->get();

                foreach ($rules as $rule) {
                    $match = false;
                    switch ($rule->rule_type) {
                        case 'subject':
                            if (stripos($subject, $rule->keyword) !== false) $match = true;
                            break;
                        case 'body':
                            if (stripos($body, $rule->keyword) !== false) $match = true;
                            break;
                        case 'sender':
                            if (stripos($from, $rule->keyword) !== false) $match = true;
                            break;
                    }

                    if ($match) {
                        $severity = $rule->target_severity;
                        // If the rule has a target client, it overrides the auto-detected one
                        if ($rule->target_client_id) {
                            $clientId = $rule->target_client_id;
                        }
                        $this->info("Matched rule: {$rule->keyword} -> $severity");
                        break;
                    }
                }

                $messageId = $message->getMessageId();

                // Check for duplicates
                if (Alert::where('message_id', $messageId)->exists()) {
                    $this->warn("Skipping duplicate message: $subject (ID: $messageId)");
                    $message->setFlag(['\Seen']);
                    continue;
                }

                $alert = Alert::create([
                    'message_id' => $messageId,
                    'subject' => $subject,
                    'description' => (string) $body,
                    'severity' => $severity,
                    'status' => 'new',
                    'client_id' => $clientId,
                    'ticket_number' => 'TKT-' . time() . '-' . Str::random(5),
                    'device' => null,
                ]);

                AlertHistory::create([
                    'alert_id' => $alert->id,
                    'action' => 'created',
                    'details' => "Imported from email. Message ID: " . $messageId,
                ]);

                // Mark as read
                $message->setFlag(['\Seen']);
            }

            Setting::set('last_fetch_at', now()->toDateTimeString());
            $this->info('Done.');

        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            $this->error('File: ' . $e->getFile() . ':' . $e->getLine());
            return 1;
        }
    }

    /**
     * Refresh the OAuth token
     */
    protected function refreshToken(OAuthToken $token): ?OAuthToken
    {
        try {
            $provider = new Google([
                'clientId'     => env('GOOGLE_CLIENT_ID'),
                'clientSecret' => env('GOOGLE_CLIENT_SECRET'),
                'redirectUri'  => env('GOOGLE_REDIRECT_URI'),
            ]);

            $newToken = $provider->getAccessToken('refresh_token', [
                'refresh_token' => $token->refresh_token
            ]);

            $token->update([
                'access_token' => $newToken->getToken(),
                'expires_at' => Carbon::createFromTimestamp($newToken->getExpires()),
            ]);

            $this->info('Token refreshed successfully.');
            
            return $token->fresh();

        } catch (\Exception $e) {
            $this->error('Failed to refresh token: ' . $e->getMessage());
            return null;
        }
    }
}
