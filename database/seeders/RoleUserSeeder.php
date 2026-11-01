<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class RoleUserSeeder extends Seeder
{
    /**
     * Creates the first super admin. In production, ADMIN_EMAIL and ADMIN_PASSWORD must be set —
     * this refuses to seed a guessable password outside local/testing.
     */
    public function run(): void
    {
        $email = env('ADMIN_EMAIL', 'admin@buntyfun.com');
        $password = env('ADMIN_PASSWORD');

        if (! $password) {
            if (! app()->environment(['local', 'testing'])) {
                $this->command?->error('ADMIN_PASSWORD must be set in the environment to seed a production admin.');

                return;
            }

            $password = Str::random(16);
            $this->command?->warn("No ADMIN_PASSWORD set — generated a local dev password: {$password}");
        }

        User::updateOrCreate(
            ['email' => $email],
            [
                'name' => 'Super Admin',
                'password' => $password,
                'role' => 'super_admin',
                'email_verified_at' => now(),
            ]
        );
    }
}
