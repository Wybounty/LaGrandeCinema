<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CinemaSetting extends Model
{
    protected $fillable = ['ticket_price'];

    protected function casts(): array
    {
        return [
            'ticket_price' => 'decimal:2',
        ];
    }

    public static function ticketPrice(): float
    {
        return (float) static::query()->value('ticket_price');
    }
}
