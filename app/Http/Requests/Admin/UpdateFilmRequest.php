<?php

namespace App\Http\Requests\Admin;

use App\FilmGenre;
use App\Models\Film;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateFilmRequest extends FormRequest
{
    public function authorize(): bool
    {
        $film = $this->route('film');

        return $film instanceof Film && ($this->user()?->can('update', $film) ?? false);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'synopsis' => ['required', 'string', 'max:5000'],
            'duration' => ['required', 'integer', 'min:1', 'max:600'],
            'genre' => ['required', Rule::enum(FilmGenre::class)],
            'poster' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
        ];
    }
}
