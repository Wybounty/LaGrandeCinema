<?php

namespace App\Mail;

use App\Models\Reservation;
use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReservationTicketsMail extends Mailable
{
    use Queueable, SerializesModels;

    /** @var array<int, string> */
    public array $qrDataUris;

    public function __construct(public Reservation $reservation)
    {
        $this->reservation->loadMissing([
            'tickets',
            'cinemaSession.film',
            'cinemaSession.room',
            'user',
        ]);

        $this->qrDataUris = $this->reservation->tickets
            ->mapWithKeys(fn ($ticket) => [
                $ticket->id => Ticket::qrDataUri($ticket->qr_code),
            ])
            ->all();
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Vos billets — '.config('services.cinema.name'),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.reservation-tickets',
        );
    }
}
