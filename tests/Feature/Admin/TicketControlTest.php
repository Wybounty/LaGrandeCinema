<?php

use App\Models\Reservation;
use App\Models\Ticket;
use App\Models\User;
use App\ReservationStatus;
use Illuminate\Support\Str;

test('spectator cannot access ticket control page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('admin.ticket-control.index'))
        ->assertForbidden();
});

test('administrator can access ticket control page', function () {
    $admin = User::factory()->administrateur()->create();

    $this->actingAs($admin)
        ->get(route('admin.ticket-control.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('admin/ticket-control/Index'));
});

test('spectator cannot verify tickets', function () {
    $user = User::factory()->create();
    $ticket = Ticket::factory()->create();

    $this->actingAs($user)
        ->postJson(route('admin.ticket-control.verify'), [
            'qr_code' => $ticket->qr_code,
        ])
        ->assertForbidden();
});

test('verify returns invalid for unknown qr code', function () {
    $admin = User::factory()->administrateur()->create();

    $this->actingAs($admin)
        ->postJson(route('admin.ticket-control.verify'), [
            'qr_code' => (string) Str::uuid(),
        ])
        ->assertOk()
        ->assertJson([
            'outcome' => 'invalid',
            'ticket' => null,
        ]);
});

test('verify accepts a valid ticket from a confirmed reservation', function () {
    $admin = User::factory()->administrateur()->create();
    $reservation = Reservation::factory()->create([
        'status' => ReservationStatus::Confirmed,
    ]);
    $ticket = Ticket::factory()->create([
        'reservation_id' => $reservation->id,
        'status' => 'valid',
    ]);

    $this->actingAs($admin)
        ->postJson(route('admin.ticket-control.verify'), [
            'qr_code' => $ticket->qr_code,
        ])
        ->assertOk()
        ->assertJson([
            'outcome' => 'valid',
            'ticket' => [
                'id' => $ticket->id,
                'qr_code' => $ticket->qr_code,
                'status' => 'valid',
            ],
        ]);
});

test('verify rejects a cancelled ticket', function () {
    $admin = User::factory()->administrateur()->create();
    $ticket = Ticket::factory()->create(['status' => 'cancelled']);

    $this->actingAs($admin)
        ->postJson(route('admin.ticket-control.verify'), [
            'qr_code' => $ticket->qr_code,
        ])
        ->assertOk()
        ->assertJson([
            'outcome' => 'cancelled',
        ]);
});

test('verify rejects an already used ticket', function () {
    $admin = User::factory()->administrateur()->create();
    $ticket = Ticket::factory()->create(['status' => 'used']);

    $this->actingAs($admin)
        ->postJson(route('admin.ticket-control.verify'), [
            'qr_code' => $ticket->qr_code,
        ])
        ->assertOk()
        ->assertJson([
            'outcome' => 'used',
        ]);
});

test('verify rejects tickets when reservation is not confirmed', function () {
    $admin = User::factory()->administrateur()->create();
    $reservation = Reservation::factory()->pending()->create();
    $ticket = Ticket::factory()->create([
        'reservation_id' => $reservation->id,
        'status' => 'valid',
    ]);

    $this->actingAs($admin)
        ->postJson(route('admin.ticket-control.verify'), [
            'qr_code' => $ticket->qr_code,
        ])
        ->assertOk()
        ->assertJson([
            'outcome' => 'invalid',
        ]);
});

test('mark used updates ticket status from valid to used', function () {
    $admin = User::factory()->administrateur()->create();
    $reservation = Reservation::factory()->create([
        'status' => ReservationStatus::Confirmed,
    ]);
    $ticket = Ticket::factory()->create([
        'reservation_id' => $reservation->id,
        'status' => 'valid',
    ]);

    $this->actingAs($admin)
        ->postJson(route('admin.ticket-control.mark-used'), [
            'qr_code' => $ticket->qr_code,
        ])
        ->assertOk()
        ->assertJson([
            'success' => true,
            'outcome' => 'used',
        ]);

    expect($ticket->fresh()->status)->toBe('used');
});

test('mark used does not affect other tickets on the same reservation', function () {
    $admin = User::factory()->administrateur()->create();
    $reservation = Reservation::factory()->create([
        'status' => ReservationStatus::Confirmed,
    ]);
    $first = Ticket::factory()->create([
        'reservation_id' => $reservation->id,
        'place_number' => 1,
        'status' => 'valid',
    ]);
    $second = Ticket::factory()->create([
        'reservation_id' => $reservation->id,
        'place_number' => 2,
        'status' => 'valid',
    ]);

    $this->actingAs($admin)
        ->postJson(route('admin.ticket-control.mark-used'), [
            'qr_code' => $first->qr_code,
        ])
        ->assertOk();

    expect($first->fresh()->status)->toBe('used')
        ->and($second->fresh()->status)->toBe('valid');
});

test('a used ticket cannot be validated again', function () {
    $admin = User::factory()->administrateur()->create();
    $ticket = Ticket::factory()->create(['status' => 'used']);

    $this->actingAs($admin)
        ->postJson(route('admin.ticket-control.mark-used'), [
            'qr_code' => $ticket->qr_code,
        ])
        ->assertOk()
        ->assertJson([
            'outcome' => 'used',
        ])
        ->assertJsonMissing(['success' => true]);

    expect($ticket->fresh()->status)->toBe('used');
});

test('only the first mark used request succeeds for the same ticket', function () {
    $admin = User::factory()->administrateur()->create();
    $reservation = Reservation::factory()->create([
        'status' => ReservationStatus::Confirmed,
    ]);
    $ticket = Ticket::factory()->create([
        'reservation_id' => $reservation->id,
        'status' => 'valid',
    ]);

    $this->actingAs($admin)
        ->postJson(route('admin.ticket-control.mark-used'), [
            'qr_code' => $ticket->qr_code,
        ])
        ->assertOk()
        ->assertJson(['success' => true]);

    $this->actingAs($admin)
        ->postJson(route('admin.ticket-control.mark-used'), [
            'qr_code' => $ticket->qr_code,
        ])
        ->assertOk()
        ->assertJsonMissing(['success' => true])
        ->assertJson(['outcome' => 'used']);

    expect($ticket->fresh()->status)->toBe('used');
});
