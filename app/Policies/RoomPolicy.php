<?php

namespace App\Policies;

use App\Models\Room;
use App\Models\User;

class RoomPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canManageUsers();
    }

    public function create(User $user): bool
    {
        return $user->canManageUsers();
    }

    public function update(User $user, Room $room): bool
    {
        return $user->canManageUsers();
    }

    public function delete(User $user, Room $room): bool
    {
        return $user->canManageUsers();
    }
}
