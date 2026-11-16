<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    /**
     * Compte de développement documenté (identifiants fixes pour les tests locaux).
     */
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'admin@legrandcinema.test'],
            [
                'name' => 'Administrateur',
                'role' => 'super_administrateur',
                'password' => 'CinemaAdmin2026!',
                'email_verified_at' => now(),
            ],
        );
    }
}