<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class SetupController extends Controller
{
    public function index()
    {
        if (config('app.installed')) {
            return redirect()->route('login');
        }

        return view('setup.index');
    }

    public function checkRequirements()
    {
        $requirements = [
            'PHP Version >= 8.1' => version_compare(PHP_VERSION, '8.1.0', '>='),
            'BCMath Extension' => extension_loaded('bcmath'),
            'Ctype Extension' => extension_loaded('ctype'),
            'Fileinfo Extension' => extension_loaded('fileinfo'),
            'JSON Extension' => extension_loaded('json'),
            'Mbstring Extension' => extension_loaded('mbstring'),
            'OpenSSL Extension' => extension_loaded('openssl'),
            'PDO Extension' => extension_loaded('pdo'),
            'Tokenizer Extension' => extension_loaded('tokenizer'),
            'XML Extension' => extension_loaded('xml'),
            'Zip Extension' => extension_loaded('zip'),
            '.env Writable' => File::isWritable(base_path('.env')),
            'Storage Writable' => File::isWritable(storage_path()),
        ];

        return view('setup.requirements', compact('requirements'));
    }

    public function debugEnv()
    {
        // Clear caches to ensure we see fresh data and routes
        Artisan::call('route:clear');
        Artisan::call('config:clear');

        $path = base_path('.env');
        return response()->json([
            'environment' => [
                'exists' => file_exists($path),
                'writable' => is_writable($path),
                'permissions' => substr(sprintf('%o', fileperms($path)), -4),
                'owner' => posix_getpwuid(fileowner($path)),
                'group' => posix_getgrgid(filegroup($path)),
                'whoami' => exec('whoami'),
            ],
            'users' => \App\Models\User::all(['id', 'name', 'email'])->toArray(),
            'route_cache_cleared' => true,
        ]);
    }

    public function showDatabaseForm()
    {
        return view('setup.database');
    }

    public function configureDatabase(Request $request)
    {
        $request->validate([
            'db_host' => 'required',
            'db_port' => 'required',
            'db_database' => 'required',
            'db_username' => 'required',
        ]);

        try {
            // Test connection
            $config = config('database.connections.mysql');
            $test_config = array_merge($config, [
                'host' => $request->db_host,
                'port' => $request->db_port,
                'database' => $request->db_database,
                'username' => $request->db_username,
                'password' => $request->db_password ?? 'root', // Default to root if empty
            ]);

            // Set temporary config to test
            config(['database.connections.setup_test' => $test_config]);
            DB::connection('setup_test')->getPdo();

            // Update .env
            $this->updateEnv([
                'DB_HOST' => $request->db_host,
                'DB_PORT' => $request->db_port,
                'DB_DATABASE' => $request->db_database,
                'DB_USERNAME' => $request->db_username,
                'DB_PASSWORD' => $request->db_password ?? 'root',
            ]);

            return redirect()->route('setup.migrate');

        } catch (\Exception $e) {
            return back()->with('error', 'Could not connect to database: ' . $e->getMessage());
        }
    }

    public function runMigrations()
    {
        try {
            Artisan::call('migrate', ['--force' => true]);
            Artisan::call('db:seed', ['--force' => true]);
            
            return view('setup.migrate_complete');
        } catch (\Exception $e) {
            return view('setup.migrate_error', ['error' => $e->getMessage()]);
        }
    }

    public function showAdminForm()
    {
        return view('setup.admin');
    }

    public function createAdmin(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $user->assignRole('admin');

        return redirect()->route('setup.email');
    }

    public function showEmailForm()
    {
        return view('setup.email');
    }

    public function configureEmail(Request $request)
    {
        // This handles standard SMTP or sets placeholders for OAuth
        $this->updateEnv([
            'MAIL_MAILER' => $request->mailer ?? 'smtp',
            'MAIL_HOST' => $request->host ?? '',
            'MAIL_PORT' => $request->port ?? '',
            'MAIL_USERNAME' => $request->username ?? '',
            'MAIL_PASSWORD' => $request->password ?? '',
            'MAIL_ENCRYPTION' => $request->encryption ?? '',
            'MAIL_FROM_ADDRESS' => $request->from_address ?? '',
        ]);

        return redirect()->route('setup.ssl');
    }

    public function showSslForm()
    {
        return view('setup.ssl');
    }

    public function generateSsl()
    {
        $domain = request('domain', 'localhost');
        $certPath = base_path('docker/certs/server.crt');
        $keyPath = base_path('docker/certs/server.key');

        // Simple self-signed generation via openssl
        $command = "openssl req -x509 -nodes -days 365 -newkey rsa:2048 -keyout $keyPath -out $certPath -subj \"/C=US/ST=State/L=City/O=Organization/CN=$domain\"";
        
        exec($command, $output, $returnVar);

        if ($returnVar === 0) {
            return back()->with('success', 'SSL Certificate generated successfully!');
        } else {
            return back()->with('error', 'Failed to generate SSL certificate. Ensure OpenSSL is installed.');
        }
    }

    public function finish()
    {
        $this->updateEnv(['APP_INSTALLED' => 'true']);
        return view('setup.finish');
    }

    private function updateEnv(array $data)
    {
        $path = base_path('.env');

        if (File::exists($path)) {
            $content = File::get($path);

            foreach ($data as $key => $value) {
                // Check if key exists
                if (strpos($content, $key . '=') !== false) {
                    $content = preg_replace("/^{$key}=.*/m", "{$key}={$value}", $content);
                } else {
                    $content .= "\n{$key}={$value}";
                }
            }

            File::put($path, $content);
        }
    }
}
