<?php

namespace App\Http\Requests\Admin\Concerns;

use App\Models\CinemaSession;
use App\Models\Film;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Validator;

trait ValidatesCinemaSessionSchedule
{
    protected function prepareForValidation(): void
    {
        $date = $this->string('session_date')->toString();
        $startTime = $this->string('start_time')->toString();
        $endTime = $this->string('end_time')->toString();

        if ($date === '' || $startTime === '' || $endTime === '') {
            return;
        }

        $this->merge([
            'starts_at' => "{$date} {$startTime}:00",
            'ends_at' => "{$date} {$endTime}:00",
        ]);
    }

    public function withValidator(Validator $validator): void
    {
        $session = $this->route('cinemaSession');
        $ignoreSessionId = $session instanceof CinemaSession ? $session->id : null;

        $validator->after(function (Validator $validator) use ($ignoreSessionId): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $startsAt = Carbon::parse($this->input('starts_at'))->seconds(0);
            $endsAt = Carbon::parse($this->input('ends_at'))->seconds(0);

            if ($endsAt->lessThanOrEqualTo($startsAt)) {
                $validator->errors()->add('ends_at', "L'heure de fin doit être postérieure à l'heure de début.");

                return;
            }

            $film = Film::query()->find($this->integer('film_id'));

            if ($film === null) {
                return;
            }

            $expectedEnd = $startsAt->copy()->addMinutes($film->duration)->seconds(0);

            if (! $expectedEnd->equalTo($endsAt->copy()->seconds(0))) {
                $validator->errors()->add(
                    'ends_at',
                    "L'heure de fin doit correspondre à la durée du film ({$film->duration} minutes).",
                );
            }

            $roomId = $this->integer('room_id');

            $hasOverlap = CinemaSession::query()
                ->where('room_id', $roomId)
                ->when($ignoreSessionId !== null, fn ($query) => $query->where('id', '!=', $ignoreSessionId))
                ->where('starts_at', '<', $endsAt)
                ->where('ends_at', '>', $startsAt)
                ->exists();

            if ($hasOverlap) {
                $validator->errors()->add(
                    'room_id',
                    'Cette salle est déjà occupée sur ce créneau horaire.',
                );
            }
        });
    }
}
