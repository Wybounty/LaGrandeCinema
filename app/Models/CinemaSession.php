<?php

namespace App\Models;

use App\ReservationStatus;
use Database\Factories\CinemaSessionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $film_id
 * @property int $room_id
 * @property Carbon $starts_at
 * @property Carbon $ends_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Film $film
 * @property-read Room $room
 */
#[Fillable(['film_id', 'room_id', 'starts_at', 'ends_at'])]
class CinemaSession extends Model
{
    /** @use HasFactory<CinemaSessionFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    /**
     * @param  Builder<CinemaSession>  $query
     */
    public function scopeUpcoming(Builder $query): void
    {
        $query->where('starts_at', '>=', now());
    }

    /**
     * @return HasMany<Reservation, $this>
     */
    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    public function remainingSeats(): int
    {
        $this->loadMissing('room');

        $confirmedPlaces = (int) $this->reservations()
            ->where('status', ReservationStatus::Confirmed)
            ->sum('number_of_places');

        return max(0, $this->room->capacity - $confirmedPlaces);
    }

    /**
     * @return BelongsTo<Film, $this>
     */
    public function film(): BelongsTo
    {
        return $this->belongsTo(Film::class);
    }

    /**
     * @return BelongsTo<Room, $this>
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }
}
