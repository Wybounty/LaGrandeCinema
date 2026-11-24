<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

class UserSettingsEndpoints
{
    private const TAG = 'Paramètres utilisateur';

    #[OA\Get(
        path: '/settings',
        operationId: 'settingsRedirect',
        description: 'Redirection permanente vers `/settings/profile` (`routes/settings.php`).',
        summary: 'Paramètres — redirection vers le profil',
        security: [['sessionCookie' => []]],
        tags: [self::TAG],
        responses: [
            new OA\Response(response: 302, description: 'Redirection vers `/settings/profile`.'),
        ],
    )]
    public function redirectToProfile(): void {}

    #[OA\Get(
        path: '/settings/profile',
        operationId: 'settingsProfileEdit',
        description: '`Settings\\ProfileController@edit` — '.InertiaWebResponses::AUTH_ONLY,
        summary: 'Profil — afficher (Inertia)',
        security: [['sessionCookie' => []]],
        tags: [self::TAG],
        responses: [new OA\Response(response: 200, ref: '#/components/responses/InertiaPage')],
    )]
    public function profileEdit(): void {}

    #[OA\Patch(
        path: '/settings/profile',
        operationId: 'settingsProfileUpdate',
        description: '`ProfileUpdateRequest` (name, email).',
        summary: 'Profil — mettre à jour',
        security: [['sessionCookie' => []], ['csrfToken' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'email'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', maxLength: 255),
                    new OA\Property(property: 'email', type: 'string', format: 'email'),
                ],
                type: 'object',
            ),
        ),
        tags: [self::TAG],
        responses: [
            new OA\Response(response: 302, ref: '#/components/responses/WebRedirect'),
            new OA\Response(response: 422, description: 'Erreur de validation.', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')),
        ],
    )]
    public function profileUpdate(): void {}

    #[OA\Delete(
        path: '/settings/profile',
        operationId: 'settingsProfileDestroy',
        description: '`Settings\\ProfileController@destroy` — `ProfileDeleteRequest`. '.InertiaWebResponses::AUTH_VERIFIED,
        summary: 'Profil — supprimer le compte',
        security: [['sessionCookie' => []], ['csrfToken' => []]],
        tags: [self::TAG],
        responses: [
            new OA\Response(response: 302, ref: '#/components/responses/WebRedirect'),
            new OA\Response(response: 422, description: 'Erreur de validation.', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')),
        ],
    )]
    public function profileDestroy(): void {}

    #[OA\Get(
        path: '/settings/security',
        operationId: 'settingsSecurityEdit',
        description: '`Settings\\SecurityController@edit` — middleware supplémentaire `RequirePassword`.',
        summary: 'Sécurité — afficher (Inertia)',
        security: [['sessionCookie' => []]],
        tags: [self::TAG],
        responses: [new OA\Response(response: 200, ref: '#/components/responses/InertiaPage')],
    )]
    public function securityEdit(): void {}

    #[OA\Put(
        path: '/settings/password',
        operationId: 'settingsPasswordUpdate',
        description: '`PasswordUpdateRequest` — limitation `throttle:6,1`.',
        summary: 'Mot de passe — mettre à jour',
        security: [['sessionCookie' => []], ['csrfToken' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['current_password', 'password', 'password_confirmation'],
                properties: [
                    new OA\Property(property: 'current_password', type: 'string', format: 'password'),
                    new OA\Property(property: 'password', type: 'string', format: 'password'),
                    new OA\Property(property: 'password_confirmation', type: 'string', format: 'password'),
                ],
                type: 'object',
            ),
        ),
        tags: [self::TAG],
        responses: [
            new OA\Response(response: 302, ref: '#/components/responses/WebRedirect'),
            new OA\Response(response: 422, description: 'Erreur de validation.', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')),
            new OA\Response(response: 429, description: 'Trop de tentatives (throttle).'),
        ],
    )]
    public function passwordUpdate(): void {}

    #[OA\Get(
        path: '/settings/appearance',
        operationId: 'settingsAppearance',
        description: 'Route `Route::inertia` — composant Vue `settings/Appearance` (thème clair/sombre).',
        summary: 'Apparence — page (Inertia)',
        security: [['sessionCookie' => []]],
        tags: [self::TAG],
        responses: [new OA\Response(response: 200, ref: '#/components/responses/InertiaPage')],
    )]
    public function appearance(): void {}
}
