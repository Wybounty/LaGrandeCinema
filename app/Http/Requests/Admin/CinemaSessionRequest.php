<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Admin\Concerns\ValidatesCinemaSessionSchedule;
use App\Models\CinemaSession;
use Illuminate\Foundation\Http\FormRequest;

class CinemaSessionRequest extends FormRequest
{
    use ValidatesCinemaSessionSchedule;

    public function authorize(): bool
    {
        $session = $this->route('cinemaSession');

        if ($session instanceof CinemaSession) {
            return $this->user()?->can('update', $session) ?? false;
        }

        return $this->user()?->can('create', CinemaSession::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'film_id' => ['required', 'integer', 'exists:films,id'],
            'room_id' => ['required', 'integer', 'exists:rooms,id'],
            'session_date' => ['required', 'date'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
        ];
    }
}
