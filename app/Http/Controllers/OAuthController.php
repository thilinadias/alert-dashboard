<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use League\OAuth2\Client\Provider\Google;
use App\Models\OAuthToken;
use Carbon\Carbon;

class OAuthController extends Controller
{
    protected function getProvider()
    {
        return new Google([
            'clientId'     => env('GOOGLE_CLIENT_ID'),
            'clientSecret' => env('GOOGLE_CLIENT_SECRET'),
            'redirectUri'  => env('GOOGLE_REDIRECT_URI'),
        ]);
    }

    public function redirectToGoogle()
    {
        $provider = $this->getProvider();

        $authUrl = $provider->getAuthorizationUrl([
            'scope' => [
                'https://mail.google.com/', // Full Gmail access (IMAP)
            ],
            'access_type' => 'offline', // Request refresh token
            'prompt' => 'consent', // Force consent screen to get refresh token
        ]);

        // Store state in session for CSRF protection
        session(['oauth2state' => $provider->getState()]);

        return redirect($authUrl);
    }

    public function handleGoogleCallback(Request $request)
    {
        // Verify state to prevent CSRF
        if (empty($request->state) || ($request->state !== session('oauth2state'))) {
            session()->forget('oauth2state');
            return redirect('/')->with('error', 'Invalid state parameter');
        }

        $provider = $this->getProvider();

        try {
            // Get access token
            $token = $provider->getAccessToken('authorization_code', [
                'code' => $request->code
            ]);

            // Get user details
            $ownerDetails = $provider->getResourceOwner($token);
            $email = $ownerDetails->getEmail();

            // Store or update token
            OAuthToken::updateOrCreate(
                ['email' => $email],
                [
                    'access_token' => $token->getToken(),
                    'refresh_token' => $token->getRefreshToken(),
                    'expires_at' => Carbon::createFromTimestamp($token->getExpires()),
                ]
            );

            return redirect('/dashboard')->with('success', "OAuth token saved for {$email}. You can now fetch emails!");

        } catch (\Exception $e) {
            return redirect('/')->with('error', 'Failed to get access token: ' . $e->getMessage());
        }
    }
}
