<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreRoomRequest;
use App\Http\Requests\Admin\UpdateRoomRequest;
use App\Models\Room;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class RoomController extends Controller
{
    public function index(): Response
    {
        $this->authorize('viewAny', Room::class);

        $rooms = Room::query()
            ->withCount('cinemaSessions')
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString()
            ->through(fn (Room $room): array => [
                'id' => $room->id,
                'name' => $room->name,
                'capacity' => $room->capacity,
                'cinema_sessions_count' => $room->cinema_sessions_count,
                'can' => [
                    'delete' => $room->cinema_sessions_count === 0,
                ],
            ]);

        return Inertia::render('admin/rooms/Index', [
            'rooms' => $rooms,
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Room::class);

        return Inertia::render('admin/rooms/Create');
    }

    public function store(StoreRoomRequest $request): RedirectResponse
    {
        Room::query()->create([
            'name' => $request->string('name')->toString(),
            'capacity' => $request->integer('capacity'),
        ]);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Salle créée.',
        ]);

        return redirect()->route('admin.rooms.index');
    }

    public function edit(Room $room): Response
    {
        $this->authorize('update', $room);

        return Inertia::render('admin/rooms/Edit', [
            'room' => [
                'id' => $room->id,
                'name' => $room->name,
                'capacity' => $room->capacity,
            ],
        ]);
    }

    public function update(UpdateRoomRequest $request, Room $room): RedirectResponse
    {
        $room->update([
            'name' => $request->string('name')->toString(),
            'capacity' => $request->integer('capacity'),
        ]);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Salle mise à jour.',
        ]);

        return redirect()->route('admin.rooms.index');
    }

    public function destroy(Room $room): RedirectResponse
    {
        $this->authorize('delete', $room);

        if ($room->cinemaSessions()->exists()) {
            Inertia::flash('toast', [
                'type' => 'error',
                'message' => 'Impossible de supprimer cette salle : des séances y sont programmées.',
            ]);

            return redirect()->route('admin.rooms.index');
        }

        $room->delete();

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Salle supprimée.',
        ]);

        return redirect()->route('admin.rooms.index');
    }
}
