<?php

namespace App\Http\Requests\Admin;

use App\Models\Room;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRoomRequest extends FormRequest
{
    public function authorize(): bool
    {
        $room = $this->route('room');

        return $room instanceof Room && ($this->user()?->can('update', $room) ?? false);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var Room $room */
        $room = $this->route('room');

        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('rooms', 'name')->ignore($room->id)],
            'capacity' => ['required', 'integer', 'min:1', 'max:1000'],
        ];
    }
}
