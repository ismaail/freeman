<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\Process\Process;

class FreemanInstall extends Command
{
    protected $signature = 'freeman:install';

    protected $description = 'Set up Freeman for self-hosting (env, migrations, super admin)';

    public function handle(): int
    {
        $this->info('');
        $this->info('  ███████╗██████╗ ███████╗███████╗███╗   ███╗ █████╗ ███╗   ██╗');
        $this->info('  ██╔════╝██╔══██╗██╔════╝██╔════╝████╗ ████║██╔══██╗████╗  ██║');
        $this->info('  █████╗  ██████╔╝█████╗  █████╗  ██╔████╔██║███████║██╔██╗ ██║');
        $this->info('  ██╔══╝  ██╔══██╗██╔══╝  ██╔══╝  ██║╚██╔╝██║██╔══██║██║╚██╗██║');
        $this->info('  ██║     ██║  ██║███████╗███████╗██║ ╚═╝ ██║██║  ██║██║ ╚████║');
        $this->info('  ╚═╝     ╚═╝  ╚═╝╚══════╝╚══════╝╚═╝     ╚═╝╚═╝  ╚═╝╚═╝  ╚═══╝');
        $this->info('');
        $this->line('  Self-hosted REST API Client — Installation Wizard');
        $this->line('  ─────────────────────────────────────────────────');
        $this->info('');

        // Step 0: Composer dependencies
        $this->stepComposer();

        // Step 1: System requirements
        $this->stepRequirements();

        // Step 1: .env file
        $this->stepEnv();

        // Step 2: App key
        $this->stepAppKey();

        // Step 3: SQLite database file
        $this->stepDatabase();

        // Step 4: Storage permissions (Linux only)
        $this->stepPermissions();

        // Step 5: Migrations
        $this->stepMigrations();

        // Step 6: Super admin
        $this->stepSuperAdmin();

        // Done
        $this->info('');
        $this->line('  ─────────────────────────────────────────────────');
        $this->info('  ✓  Freeman is ready!');
        $this->info('');
        $appUrl = config('app.url', 'http://localhost:8000');
        $this->line("  Open your browser and go to: <href={$appUrl}>{$appUrl}</>");
        $this->line('  Run the server with:  php artisan serve');
        $this->info('');

        return self::SUCCESS;
    }

    private function stepComposer(): void
    {
        $this->line('  [0/6] Composer dependencies');

        if (is_dir(base_path('vendor')) && file_exists(base_path('vendor/autoload.php'))) {
            $this->line('        vendor/ already present — skipping.');
            return;
        }

        $this->line('        Running composer install...');
        $this->info('');

        $process = new Process(['composer', 'install'], base_path());
        $process->setTimeout(300);

        try {
            $process->setTty(true);
            $process->run();
        } catch (\Throwable) {
            // TTY not available (e.g. piped input) — fall back to inherited I/O
            $process->setTty(false);
            $process->setInput(STDIN);
            $process->run(function (string $_type, string $output) {
                $this->getOutput()->write($output);
            });
        }

        if (! $process->isSuccessful()) {
            $this->error('        composer install failed. Fix the errors above and re-run.');
            exit(self::FAILURE);
        }

        $this->info('');
        $this->line('        Dependencies installed.');
    }

    private function stepRequirements(): void
    {
        $this->line('  [1/6] Checking requirements');

        $missing = [];

        if (! extension_loaded('pdo_sqlite')) {
            $missing[] = 'pdo_sqlite';
        }

        if (! extension_loaded('openssl')) {
            $missing[] = 'openssl';
        }

        if (empty($missing)) {
            $this->line('        All requirements met.');
            return;
        }

        $this->error('        Missing PHP extensions: ' . implode(', ', $missing));
        $this->info('');
        $this->line('        Install them and re-run:');
        $this->line('');
        $this->line('          Ubuntu/Debian:  sudo apt install php-sqlite3 php-curl');
        $this->line('          RHEL/Fedora:    sudo dnf install php-pdo php-sqlite3');
        $this->line('          macOS (Brew):   already included in php formula');
        $this->line('          Windows:        enable extension=pdo_sqlite in php.ini');
        $this->info('');

        exit(self::FAILURE);
    }

    private function stepEnv(): void
    {
        $this->line('  [2/6] Environment file');

        if (file_exists(base_path('.env'))) {
            $this->line('        .env already exists — skipping copy.');
            return;
        }

        if (! file_exists(base_path('.env.example'))) {
            $this->error('        .env.example not found. Cannot create .env.');
            exit(self::FAILURE);
        }

        copy(base_path('.env.example'), base_path('.env'));
        $this->line('        Copied .env.example → .env');
    }

    private function stepAppKey(): void
    {
        $this->line('  [3/6] Application key');

        $key = config('app.key');

        if (! empty($key) && str_starts_with($key, 'base64:')) {
            $this->line('        Key already set — skipping.');
            return;
        }

        $this->call('key:generate', ['--force' => true]);
    }

    private function stepDatabase(): void
    {
        $this->line('  [4/7] SQLite database');

        $dbPath = database_path('database.sqlite');

        if (file_exists($dbPath)) {
            $this->line('        database.sqlite already exists — skipping.');
            return;
        }

        touch($dbPath);
        $this->line('        Created database/database.sqlite');
    }

    private function stepPermissions(): void
    {
        $this->line('  [5/7] Storage permissions');

        if (PHP_OS_FAMILY !== 'Linux') {
            $this->line('        Skipped (not Linux).');
            return;
        }

        $base    = base_path();
        $storage = storage_path();
        $cache   = base_path('bootstrap/cache');

        if (function_exists('posix_getuid') && posix_getuid() === 0) {
            exec("chown -R www-data:www-data " . escapeshellarg($base));
            exec("chmod -R 755 " . escapeshellarg($storage));
            exec("chmod -R 755 " . escapeshellarg($cache));
            $this->line('        Permissions set for www-data.');
        } else {
            $this->line('        Not running as root — set permissions manually:');
            $this->line("          sudo chown -R www-data:www-data {$base}");
            $this->line("          sudo chmod -R 755 {$storage}");
            $this->line("          sudo chmod -R 755 {$cache}");
        }
    }

    private function stepMigrations(): void
    {
        $this->line('  [6/7] Running migrations');

        $this->call('migrate', ['--force' => true]);
    }

    private function stepSuperAdmin(): void
    {
        $this->line('  [7/7] Super admin account');
        $this->info('');

        // Check if a super admin already exists
        if (User::where('is_super_admin', true)->exists()) {
            $this->line('        A super admin account already exists — skipping.');
            return;
        }

        $this->line('        Create your super admin account.');
        $this->info('');

        $username = $this->askUsername();
        $email    = $this->askEmail();
        $password = $this->askPassword();

        User::create([
            'username'             => $username,
            'email'                => $email,
            'password'             => Hash::make($password),
            'is_super_admin'       => true,
            'must_change_password' => false,
        ]);

        $this->info('');
        $this->line("        Super admin '{$username}' created successfully.");
    }

    private function askUsername(): string
    {
        do {
            $username = $this->ask('        Username');

            if (empty($username)) {
                $this->warn('        Username cannot be empty.');
                continue;
            }

            if (User::where('username', $username)->exists()) {
                $this->warn("        Username '{$username}' is already taken.");
                continue;
            }

            return $username;
        } while (true);
    }

    private function askEmail(): string
    {
        do {
            $email = $this->ask('        Email (used for password reset)');

            if (empty($email)) {
                $this->warn('        Email cannot be empty.');
                continue;
            }

            if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->warn('        Please enter a valid email address.');
                continue;
            }

            if (User::where('email', $email)->exists()) {
                $this->warn("        Email '{$email}' is already in use.");
                continue;
            }

            return $email;
        } while (true);
    }

    private function askPassword(): string
    {
        do {
            $password = $this->secret('        Password (min 8 characters)');

            if (strlen($password) < 8) {
                $this->warn('        Password must be at least 8 characters.');
                continue;
            }

            $confirm = $this->secret('        Confirm password');

            if ($password !== $confirm) {
                $this->warn('        Passwords do not match. Try again.');
                continue;
            }

            return $password;
        } while (true);
    }
}
