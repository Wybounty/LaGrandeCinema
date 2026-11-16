<?php

namespace Database\Factories;

use App\FilmGenre;
use App\Models\Film;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Film>
 */
class FilmFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->unique()->sentence(3);

        return [
            'title' => $title,
            'synopsis' => fake()->paragraphs(2, true),
            'duration' => fake()->numberBetween(80, 150),
            'genre' => fake()->randomElement(FilmGenre::cases())->value,
            'poster' => 'https://picsum.photos/seed/'.Str::slug($title).'/400/600',
        ];
    }
}
