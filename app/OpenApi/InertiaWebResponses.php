<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

/**
 * Réponses réutilisables pour les routes web Inertia (non-JSON).
 */
#[OA\Response(
    response: 'InertiaPage',
    description: <<<'TXT'
        **Page Inertia (interface Vue)** — ce n’est pas un endpoint JSON.
        Réponse HTML avec en-têtes `X-Inertia` / `X-Inertia-Version` et un objet `page` (composant + props) côté client.
        Les tests Pest utilisent `assertInertia()` sur ces routes.
        TXT,
    content: new OA\MediaType(
        mediaType: 'text/html',
        schema: new OA\Schema(type: 'string'),
    ),
)]
#[OA\Response(
    response: 'WebRedirect',
    description: 'Redirection HTTP après mutation réussie (formulaire Inertia). Flash toast éventuel en session.',
)]
#[OA\Response(
    response: 'WebRedirectToLogin',
    description: 'Redirection vers la connexion lorsque l’utilisateur n’est pas authentifié (401 implicite).',
)]
class InertiaWebResponses
{
    public const ADMIN_SECURITY = <<<'TXT'
        **Middleware** : `auth`, `verified`, `manage-users` (`EnsureUserCanManageUsers` — rôle `administrateur` ou `super_administrateur`).
        **Autorisation** : policies Laravel sur le modèle concerné (`authorize` dans le contrôleur ou le Form Request).
        TXT;

    public const AUTH_VERIFIED = <<<'TXT'
        **Middleware** : `auth`, `verified`.
        TXT;

    public const AUTH_ONLY = <<<'TXT'
        **Middleware** : `auth`.
        TXT;
}
