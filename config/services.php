<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | La Grande Cinéma — informations de l'entreprise
    |--------------------------------------------------------------------------
    |
    | Coordonnées et texte affichés sur le site (pied de page, e-mails, etc.).
    | Le tarif des billets est stocké en base (table cinema_settings).
    |
    */

    'cinema' => [
        'name' => 'Le Grand Cinéma',
        'tagline' => 'Cinéma historique, programmation variée et réservation en ligne pour simplifier votre venue.',
        'email' => env('CINEMA_CONTACT_EMAIL', ''),
        'phone' => env('CINEMA_CONTACT_PHONE', ''),
        'address' => env('CINEMA_ADDRESS', ''),
        'postal_code' => env('CINEMA_POSTAL_CODE', ''),
        'city' => env('CINEMA_CITY', ''),
    ],

];
