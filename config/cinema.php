<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Prix d'un billet (simulation)
    |--------------------------------------------------------------------------
    |
    | Tarif unitaire utilisé pour le calcul du montant des réservations.
    | Modifiable via la variable d'environnement CINEMA_TICKET_PRICE.
    |
    */

    'ticket_price' => (float) env('CINEMA_TICKET_PRICE', 12.5),

];
