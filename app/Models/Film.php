<?php

namespace App\Models;

use App\FilmGenre;
use Database\Factories\FilmFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $title
 * @property string $synopsis
 * @property int $duration
 * @property FilmGenre $genre
 * @property string|null $poster
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['title', 'synopsis', 'duration', 'genre', 'poster'])]
class Film extends Model
{
    /** @use HasFactory<FilmFactory> */
    use HasFactory;

    /**
     * @return HasMany<CinemaSession, $this>
     */
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'genre' => FilmGenre::class,
        ];
    }

    /**
     * @return HasMany<CinemaSession, $this>
     */
    public function cinemaSessions(): HasMany
    {
        return $this->hasMany(CinemaSession::class);
    }

    /**
     * Films ayant au moins une séance à venir.
     *
     * @param  Builder<Film>  $query
     */
    public function scopeCurrentlyScheduled(Builder $query): void
    {
        $query->whereHas(
            'cinemaSessions',
            fn (Builder $sessionsQuery): Builder => $sessionsQuery->upcoming(),
        );
    }
}
