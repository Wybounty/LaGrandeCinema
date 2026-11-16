<?php

namespace App\Policies;

use App\Models\Film;
use App\Models\User;

class FilmPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canManageUsers();
    }

    public function create(User $user): bool
    {
        return $user->canManageUsers();
    }

    public function update(User $user, Film $film): bool
    {
        return $user->canManageUsers();
    }

    public function delete(User $user, Film $film): bool
    {
        return $user->canManageUsers();
    }
}
