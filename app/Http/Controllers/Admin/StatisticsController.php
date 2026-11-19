<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CinemaSession;
use App\Models\Film;
use App\Models\Payment;
use App\Models\Reservation;
use App\Models\Room;
use App\PaymentStatus;
use App\ReservationStatus;
use App\StatisticsPeriod;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class StatisticsController extends Controller
{
    /**
     * @return array<string, mixed>
     */
    public function collect(Request $request): array
    {
        $validated = $request->validate([
            'period' => ['nullable', Rule::enum(StatisticsPeriod::class)],
        ]);

        $period = isset($validated['period'])
            ? StatisticsPeriod::from($validated['period'])
            : StatisticsPeriod::Month;

        [$startsAt, $endsAt] = $period->range();

        $reservationsInPeriod = Reservation::query()
            ->whereBetween('created_at', [$startsAt, $endsAt]);

        $confirmedInPeriod = $this->confirmedInPeriod($startsAt, $endsAt);

        $summary = [
            'reservations_total' => (clone $reservationsInPeriod)->count(),
            'reservations_confirmed' => (clone $confirmedInPeriod)->count(),
            'reservations_cancelled' => (clone $reservationsInPeriod)
                ->where('status', ReservationStatus::Cancelled)
                ->count(),
            'places_confirmed' => (int) (clone $confirmedInPeriod)->sum('number_of_places'),
            'films_scheduled' => Film::query()
                ->whereHas(
                    'cinemaSessions',
                    fn (Builder $query) => $query->whereBetween('starts_at', [$startsAt, $endsAt]),
                )
                ->count(),
            'sessions_scheduled' => CinemaSession::query()
                ->whereBetween('starts_at', [$startsAt, $endsAt])
                ->count(),
            'amount_confirmed' => $this->amountConfirmed($startsAt, $endsAt),
        ];

        return [
            'period' => $period->value,
            'periodLabel' => $period->label(),
            'periodOptions' => collect(StatisticsPeriod::cases())
                ->map(fn (StatisticsPeriod $option): array => [
                    'value' => $option->value,
                    'label' => $option->label(),
                ])
                ->all(),
            'summary' => $summary,
            'attendance' => $this->attendanceEvolution($startsAt, $endsAt),
            'occupancy' => $this->occupancy($startsAt, $endsAt),
            'popularFilms' => $this->popularFilms($startsAt, $endsAt),
        ];
    }

    /**
     * Réservations confirmées sur la période (création ou paiement Stripe réussi).
     *
     * @return Builder<Reservation>
     */
    protected function confirmedInPeriod(CarbonInterface $startsAt, CarbonInterface $endsAt): Builder
    {
        return Reservation::query()
            ->where('status', ReservationStatus::Confirmed)
            ->where(function (Builder $query) use ($startsAt, $endsAt): void {
                $query
                    ->whereBetween('created_at', [$startsAt, $endsAt])
                    ->orWhereHas(
                        'payments',
                        fn (Builder $payment) => $payment
                            ->where('status', PaymentStatus::Succeeded)
                            ->whereBetween('paid_at', [$startsAt, $endsAt]),
                    );
            });
    }

    protected function amountConfirmed(CarbonInterface $startsAt, CarbonInterface $endsAt): float
    {
        $fromPayments = (int) Payment::query()
            ->where('status', PaymentStatus::Succeeded)
            ->whereBetween('paid_at', [$startsAt, $endsAt])
            ->sum('amount');

        $legacyWithoutPayment = (float) Reservation::query()
            ->where('status', ReservationStatus::Confirmed)
            ->whereBetween('created_at', [$startsAt, $endsAt])
            ->whereDoesntHave(
                'payments',
                fn (Builder $query) => $query->where('status', PaymentStatus::Succeeded),
            )
            ->sum('total_amount');

        return round(($fromPayments / 100) + $legacyWithoutPayment, 2);
    }

    /**
     * @return array{points: list<array{date: string, reservations: int, places: int}>, totals: array{reservations: int, places: int}}
     */
    protected function attendanceEvolution(CarbonInterface $startsAt, CarbonInterface $endsAt): array
    {
        $rows = Reservation::query()
            ->selectRaw('DATE(cinema_sessions.starts_at) as session_date')
            ->selectRaw('COUNT(reservations.id) as reservations_count')
            ->selectRaw('SUM(reservations.number_of_places) as places_sum')
            ->join('cinema_sessions', 'cinema_sessions.id', '=', 'reservations.cinema_session_id')
            ->where('reservations.status', ReservationStatus::Confirmed)
            ->whereBetween('cinema_sessions.starts_at', [$startsAt, $endsAt])
            ->groupBy('session_date')
            ->orderBy('session_date')
            ->get();

        $byDate = $rows->keyBy('session_date');

        $points = [];
        $cursor = $startsAt->copy()->startOfDay();
        $endDay = $endsAt->copy()->startOfDay();

        while ($cursor->lessThanOrEqualTo($endDay)) {
            $key = $cursor->toDateString();
            $row = $byDate->get($key);

            $points[] = [
                'date' => $key,
                'reservations' => $row !== null ? (int) $row->reservations_count : 0,
                'places' => $row !== null ? (int) $row->places_sum : 0,
            ];

            $cursor = $cursor->addDay();
        }

        return [
            'points' => $points,
            'totals' => [
                'reservations' => (int) $rows->sum('reservations_count'),
                'places' => (int) $rows->sum('places_sum'),
            ],
        ];
    }

    /**
     * @return array{
     *     global: array{rate: float, reserved_places: int, total_capacity: int},
     *     rooms: list<array{room_id: int, room_name: string, rate: float, reserved_places: int, total_capacity: int}>
     * }
     */
    protected function occupancy(CarbonInterface $startsAt, CarbonInterface $endsAt): array
    {
        $sessionsQuery = CinemaSession::query()
            ->whereBetween('starts_at', [$startsAt, $endsAt])
            ->where('starts_at', '<=', now());

        $sessionIds = (clone $sessionsQuery)->pluck('id');

        $totalCapacity = (int) (clone $sessionsQuery)
            ->join('rooms', 'rooms.id', '=', 'cinema_sessions.room_id')
            ->sum('rooms.capacity');

        $reservedPlaces = $sessionIds->isEmpty()
            ? 0
            : (int) Reservation::query()
                ->whereIn('cinema_session_id', $sessionIds)
                ->where('status', ReservationStatus::Confirmed)
                ->sum('number_of_places');

        $globalRate = $totalCapacity > 0
            ? min(100, round(($reservedPlaces / $totalCapacity) * 100, 1))
            : 0.0;

        $rooms = Room::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        $roomStats = [];

        foreach ($rooms as $room) {
            $roomSessionIds = (clone $sessionsQuery)
                ->where('room_id', $room->id)
                ->pluck('cinema_sessions.id');

            if ($roomSessionIds->isEmpty()) {
                continue;
            }

            $roomCapacity = (int) DB::table('cinema_sessions')
                ->join('rooms', 'rooms.id', '=', 'cinema_sessions.room_id')
                ->whereIn('cinema_sessions.id', $roomSessionIds)
                ->sum('rooms.capacity');

            $roomReserved = (int) Reservation::query()
                ->whereIn('cinema_session_id', $roomSessionIds)
                ->where('status', ReservationStatus::Confirmed)
                ->sum('number_of_places');

            $roomStats[] = [
                'room_id' => $room->id,
                'room_name' => $room->name,
                'rate' => $roomCapacity > 0
                    ? min(100, round(($roomReserved / $roomCapacity) * 100, 1))
                    : 0.0,
                'reserved_places' => $roomReserved,
                'total_capacity' => $roomCapacity,
            ];
        }

        return [
            'global' => [
                'rate' => $globalRate,
                'reserved_places' => $reservedPlaces,
                'total_capacity' => $totalCapacity,
            ],
            'rooms' => $roomStats,
        ];
    }

    /**
     * @return list<array{
     *     id: int,
     *     title: string,
     *     poster: string|null,
     *     reservations_count: int,
     *     places_sum: int
     * }>
     */
    protected function popularFilms(CarbonInterface $startsAt, CarbonInterface $endsAt): array
    {
        return Film::query()
            ->select([
                'films.id',
                'films.title',
                'films.poster',
                DB::raw('COUNT(reservations.id) as reservations_count'),
                DB::raw('COALESCE(SUM(reservations.number_of_places), 0) as places_sum'),
            ])
            ->join('cinema_sessions', 'cinema_sessions.film_id', '=', 'films.id')
            ->join('reservations', 'reservations.cinema_session_id', '=', 'cinema_sessions.id')
            ->where('reservations.status', ReservationStatus::Confirmed)
            ->whereBetween('cinema_sessions.starts_at', [$startsAt, $endsAt])
            ->groupBy('films.id', 'films.title', 'films.poster')
            ->orderByDesc('places_sum')
            ->orderByDesc('reservations_count')
            ->limit(10)
            ->get()
            ->map(fn (Film $film): array => [
                'id' => $film->id,
                'title' => $film->title,
                'poster' => $film->poster,
                'reservations_count' => (int) $film->reservations_count,
                'places_sum' => (int) $film->places_sum,
            ])
            ->all();
    }
}
