<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = config('sureprocure.admin.email');

        if (! $email) {
            $this->command?->error('ADMIN_EMAIL is not set in .env, skipping admin user.');

            return;
        }

        if (User::withTrashed()->where('email', $email)->exists()) {
            $this->command?->info("Admin {$email} already exists, left unchanged.");

            return;
        }

        $configured = config('sureprocure.admin.password');
        $password = $configured ?: Str::password(16);

        User::forceCreate([
            'name' => config('sureprocure.admin.name', 'Admin'),
            'email' => $email,
            'password' => $password, // hashed by the User model cast
            'role' => UserRole::Admin,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $this->command?->info("Admin created: {$email}");

        if (! $configured) {
            $this->command?->warn("Generated password (shown once, change it after first login): {$password}");
        }
    }
}
