<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReservationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'cinema_session_id' => ['required', 'integer', 'exists:cinema_sessions,id'],
            'number_of_places' => ['required', 'integer', 'min:1', 'max:20'],
        ];
    }
}
