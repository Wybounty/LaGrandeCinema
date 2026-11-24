<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.1.0',
    title: 'LaGrandeCinema API',
    description: <<<'DESC'
        Documentation **OpenAPI 3** de **LaGrandeCinema** (évaluation Studi).

        ### Deux familles de routes
        1. **Endpoints JSON / machine** (`Contrôle billets (JSON)`, `Paiements`, `Billets`, `Santé`) — corps de réponse structuré.
        2. **Interface Inertia** — pages Vue via `routes/web.php` : réponse `text/html` + props Inertia, ou **redirection** après formulaire.

        **Authentification** : session Fortify (`/login`), cookie `laravel_session`, jeton CSRF `X-XSRF-TOKEN` pour les `POST`/`PATCH`/`DELETE` web.
        Aucune API REST séparée (`routes/api.php` absent).

        Les schémas de corps de requête Inertia reprennent les règles des **Form Requests** existants (pas de champs inventés).
        DESC,
    contact: new OA\Contact(
        name: 'Le Grand Cinéma',
        email: 'contact@legrandcinema.test',
    ),
)]
#[OA\Server(
    url: 'http://localhost:8000',
    description: 'Développement local (valeur `APP_URL` par défaut)',
)]
#[OA\Tag(name: 'Santé', description: 'Disponibilité de l\'application')]
#[OA\Tag(name: 'Contrôle billets (JSON)', description: 'Endpoints JSON de scan (distincts des pages Inertia)')]
#[OA\Tag(name: 'Billets', description: 'Ressources billets pour les spectateurs authentifiés')]
#[OA\Tag(name: 'Paiements', description: 'Intégration Stripe (webhook serveur à serveur)')]
#[OA\Tag(name: 'Administration – Interface Inertia', description: 'Back-office cinéma : films, séances, salles, réservations, utilisateurs, contrôle entrée (pages Vue)')]
#[OA\Tag(name: 'Tableau de bord – Interface Inertia', description: 'Accueil connecté et statistiques agrégées')]
#[OA\Tag(name: 'Paramètres utilisateur', description: 'Profil, sécurité, apparence (Inertia)')]
#[OA\SecurityScheme(
    securityScheme: 'sessionCookie',
    type: 'apiKey',
    description: 'Session Laravel établie après connexion Fortify (`/login`).',
    name: 'laravel_session',
    in: 'cookie',
)]
#[OA\SecurityScheme(
    securityScheme: 'csrfToken',
    type: 'apiKey',
    description: 'Jeton CSRF Laravel (header requis pour les `POST` web hors webhook).',
    name: 'X-XSRF-TOKEN',
    in: 'header',
)]
#[OA\SecurityScheme(
    securityScheme: 'stripeSignature',
    type: 'apiKey',
    description: 'Signature Stripe fournie par Stripe sur chaque événement webhook.',
    name: 'Stripe-Signature',
    in: 'header',
)]
class OpenApiSpec
{
}