<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canManageUsers();
    }

    public function view(User $user, User $model): bool
    {
        return $this->update($user, $model);
    }

    public function create(User $user): bool
    {
        return $user->canManageUsers();
    }

    public function update(User $user, User $model): bool
    {
        if (! $user->canManageUsers()) {
            return false;
        }

        if ($user->isAdministrateur() && $model->isSuperAdministrateur()) {
            return false;
        }

        return true;
    }

    public function delete(User $user, User $model): bool
    {
        if (! $user->canManageUsers() || $user->id === $model->id) {
            return false;
        }

        if ($user->isAdministrateur()) {
            return $model->isSpectateur();
        }

        if ($model->isSuperAdministrateur() && $this->superAdministrateurCount() <= 1) {
            return false;
        }

        return true;
    }

    public function assignSuperRole(User $user): bool
    {
        return $user->isSuperAdministrateur();
    }

    protected function superAdministrateurCount(): int
    {
        return User::query()->where('role', 'super_administrateur')->count();
    }
}
