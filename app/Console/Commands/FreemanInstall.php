<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

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

        // Step 1: .env file
        $this->stepEnv();

        // Step 2: App key
        $this->stepAppKey();

        // Step 3: SQLite database file
        $this->stepDatabase();

        // Step 4: Migrations
        $this->stepMigrations();

        // Step 5: Super admin
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

    private function stepEnv(): void
    {
        $this->line('  [1/5] Environment file');

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
        $this->line('  [2/5] Application key');

        $key = config('app.key');

        if (! empty($key) && str_starts_with($key, 'base64:')) {
            $this->line('        Key already set — skipping.');
            return;
        }

        $this->call('key:generate', ['--force' => true]);
    }

    private function stepDatabase(): void
    {
        $this->line('  [3/5] SQLite database');

        $dbPath = database_path('database.sqlite');

        if (file_exists($dbPath)) {
            $this->line('        database.sqlite already exists — skipping.');
            return;
        }

        touch($dbPath);
        $this->line('        Created database/database.sqlite');
    }

    private function stepMigrations(): void
    {
        $this->line('  [4/5] Running migrations');

        $this->call('migrate', ['--force' => true]);
    }

    private function stepSuperAdmin(): void
    {
        $this->line('  [5/5] Super admin account');
        $this->info('');

        // Check if a super admin already exists
        if (User::where('is_super_admin', true)->exists()) {
            $this->line('        A super admin account already exists — skipping.');
            return;
        }

        $this->line('        Create your super admin account.');
        $this->info('');

        $username = $this->askUsername();
        $password = $this->askPassword();

        User::create([
            'username'            => $username,
            'password'            => Hash::make($password),
            'is_super_admin'      => true,
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
