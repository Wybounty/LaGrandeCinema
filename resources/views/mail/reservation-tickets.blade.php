<x-mail::message>
# Vos billets pour {{ config('services.cinema.name') }}

Bonjour {{ $reservation->user->name }},

Votre réservation pour **{{ $reservation->cinemaSession->film->title }}** est confirmée.

**Séance :** {{ $reservation->cinemaSession->starts_at->timezone(config('app.timezone'))->translatedFormat('l j F Y · H:i') }}  
**Salle :** {{ $reservation->cinemaSession->room->name }}  
**Places :** {{ $reservation->number_of_places }}

@foreach ($reservation->tickets->sortBy('place_number') as $ticket)
---

### Billet {{ $ticket->place_number }} / {{ $reservation->number_of_places }}

**Identifiant :** `{{ $ticket->qr_code }}`

<img src="{{ $qrDataUris[$ticket->id] }}" alt="QR code billet {{ $ticket->place_number }}" width="180" height="180" />

@endforeach

Vous pouvez aussi retrouver vos billets à tout moment dans **Mes billets** sur votre espace personnel.

<x-mail::button :url="route('tickets.index')">
Mes billets
</x-mail::button>

Merci,<br>
{{ config('services.cinema.name') }}
</x-mail::message>