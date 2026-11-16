<?php

namespace App\Http\Requests\Admin;

use App\FilmGenre;
use App\Models\Film;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFilmRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Film::class) ?? false;
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
            'poster' => ['required', 'image', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
        ];
    }
}
