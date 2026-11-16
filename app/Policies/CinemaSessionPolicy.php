<?php

namespace App\Policies;

use App\Models\CinemaSession;
use App\Models\User;

class CinemaSessionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canManageUsers();
    }

    public function create(User $user): bool
    {
        return $user->canManageUsers();
    }

    public function update(User $user, CinemaSession $cinemaSession): bool
    {
        return $user->canManageUsers();
    }

    public function delete(User $user, CinemaSession $cinemaSession): bool
    {
        return $user->canManageUsers();
    }
}
