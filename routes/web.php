<?php

use App\Http\Controllers\FilmController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\TicketController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('films', [FilmController::class, 'index'])->name('films.index');
Route::get('films/{film}', [FilmController::class, 'show'])->name('films.show');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');

    Route::get('reservations/create/{cinemaSession}', [ReservationController::class, 'create'])
        ->name('reservations.create');
    Route::post('reservations', [ReservationController::class, 'store'])
        ->name('reservations.store');
    Route::get('reservations/{reservation}', [ReservationController::class, 'show'])
        ->name('reservations.show');
    Route::post('reservations/{reservation}/pay', [ReservationController::class, 'pay'])
        ->name('reservations.pay');
    Route::get('reservations/{reservation}/confirmation', [ReservationController::class, 'confirmation'])
        ->name('reservations.confirmation');

    Route::middleware('spectateur')->group(function () {
        Route::get('mes-billets', [TicketController::class, 'index'])->name('tickets.index');
        Route::get('tickets/{ticket}/qr', [TicketController::class, 'qrCode'])->name('tickets.qr');
    });
});

require __DIR__.'/settings.php';
