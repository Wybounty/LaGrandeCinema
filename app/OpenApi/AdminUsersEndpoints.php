<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

class AdminUsersEndpoints
{
    private const TAG = 'Administration – Interface Inertia';

    #[OA\Get(
        path: '/dashboard/users',
        operationId: 'adminUsersIndex',
        description: '`Admin\\UserController@index`',
        summary: 'Utilisateurs — liste (Inertia)',
        security: [['sessionCookie' => []]],
        tags: [self::TAG],
        responses: [new OA\Response(response: 200, ref: '#/components/responses/InertiaPage')],
    )]
    public function index(): void
    {
    }

    #[OA\Get(
        path: '/dashboard/users/create',
        operationId: 'adminUsersCreate',
        summary: 'Utilisateurs — formulaire de création (Inertia)',
        security: [['sessionCookie' => []]],
        tags: [self::TAG],
        responses: [new OA\Response(response: 200, ref: '#/components/responses/InertiaPage')],
    )]
    public function create(): void
    {
    }

    #[OA\Post(
        path: '/dashboard/users',
        operationId: 'adminUsersStore',
        description: '`StoreManagedUserRequest` — rôles selon le créateur (`spectateur`, `administrateur`, optionnel `super_administrateur`).',
        summary: 'Utilisateurs — créer',
        security: [['sessionCookie' => []], ['csrfToken' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'email', 'password', 'role'],
                properties: [
                    new OA\Property(property: 'name', type: 'string'),
                    new OA\Property(property: 'email', type: 'string', format: 'email'),
                    new OA\Property(property: 'password', type: 'string', format: 'password'),
                    new OA\Property(
                        property: 'role',
                        type: 'string',
                        enum: ['spectateur', 'administrateur', 'super_administrateur'],
                    ),
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
    public function store(): void
    {
    }

    #[OA\Get(
        path: '/dashboard/users/{user}/edit',
        operationId: 'adminUsersEdit',
        summary: 'Utilisateurs — édition (Inertia)',
        security: [['sessionCookie' => []]],
        tags: [self::TAG],
        parameters: [new OA\Parameter(name: 'user', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, ref: '#/components/responses/InertiaPage')],
    )]
    public function edit(): void
    {
    }

    #[OA\Patch(
        path: '/dashboard/users/{user}',
        operationId: 'adminUsersUpdate',
        description: '`UpdateManagedUserRequest` — règles métier sur le rôle (dernier super admin, auto-modification, etc.).',
        summary: 'Utilisateurs — mettre à jour',
        security: [['sessionCookie' => []], ['csrfToken' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'email', 'role'],
                properties: [
                    new OA\Property(property: 'name', type: 'string'),
                    new OA\Property(property: 'email', type: 'string', format: 'email'),
                    new OA\Property(property: 'password', type: 'string', format: 'password', nullable: true),
                    new OA\Property(property: 'password_confirmation', type: 'string', nullable: true),
                    new OA\Property(property: 'role', type: 'string'),
                ],
                type: 'object',
            ),
        ),
        tags: [self::TAG],
        parameters: [new OA\Parameter(name: 'user', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 302, ref: '#/components/responses/WebRedirect'),
            new OA\Response(response: 422, description: 'Erreur de validation.', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')),
        ],
    )]
    public function update(): void
    {
    }

    #[OA\Delete(
        path: '/dashboard/users/{user}',
        operationId: 'adminUsersDestroy',
        description: '`Admin\\UserController@destroy`',
        summary: 'Utilisateurs — supprimer',
        security: [['sessionCookie' => []], ['csrfToken' => []]],
        tags: [self::TAG],
        parameters: [new OA\Parameter(name: 'user', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 302, ref: '#/components/responses/WebRedirect')],
    )]
    public function destroy(): void
    {
    }
}