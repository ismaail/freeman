<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $username = env('SUPER_ADMIN_USERNAME', 'admin');
        $password = env('SUPER_ADMIN_PASSWORD', 'password');

        if (User::where('username', $username)->exists()) {
            $this->command->info("Super admin '{$username}' already exists, skipping.");
            return;
        }

        User::create([
            'username' => $username,
            'password' => $password,
            'is_super_admin' => true,
            'must_change_password' => false,
        ]);

        $this->command->info("Super admin '{$username}' created.");
    }
}
