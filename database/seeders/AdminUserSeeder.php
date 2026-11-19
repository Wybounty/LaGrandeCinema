<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    /**
     * Comptes de développement documentés (identifiants fixes pour les tests locaux).
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

        User::query()->updateOrCreate(
            ['email' => 'spectateur@legrandcinema.test'],
            [
                'name' => 'Jean Spectateur',
                'role' => 'spectateur',
                'password' => 'CinemaSpectateur2026!',
                'email_verified_at' => now(),
            ],
        );
    }
}
