<?php

namespace App\Policies;

use App\Models\Ticket;
use App\Models\User;

class TicketPolicy
{
    public function view(User $user, Ticket $ticket): bool
    {
        $reservation = $ticket->reservation;

        return $reservation !== null && $user->id === $reservation->user_id;
    }

    public function checkAtEntrance(User $user): bool
    {
        return $user->canManageUsers();
    }
}
