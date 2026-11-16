<?php

namespace App\Models;

use Database\Factories\FilmFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $title
 * @property string $synopsis
 * @property int $duration
 * @property string $genre
 * @property string|null $poster
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['title', 'synopsis', 'duration', 'genre', 'poster'])]
class Film extends Model
{
    /** @use HasFactory<FilmFactory> */
    use HasFactory;
}
