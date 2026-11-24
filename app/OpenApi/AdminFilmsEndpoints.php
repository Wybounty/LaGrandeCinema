<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

class AdminFilmsEndpoints
{
    private const TAG = 'Administration – Interface Inertia';

    private const CTRL = '`Admin\\FilmController`';

    #[OA\Get(
        path: '/dashboard/films',
        operationId: 'adminFilmsIndex',
        description: self::CTRL.'@index — liste paginée. '.InertiaWebResponses::ADMIN_SECURITY,
        summary: 'Films — liste (Inertia)',
        security: [['sessionCookie' => []], ['csrfToken' => []]],
        tags: [self::TAG],
        responses: [
            new OA\Response(response: 200, ref: '#/components/responses/InertiaPage'),
            new OA\Response(response: 403, description: 'Non administrateur ou policy `viewAny` refusée.'),
        ],
    )]
    public function index(): void {}

    #[OA\Get(
        path: '/dashboard/films/create',
        operationId: 'adminFilmsCreate',
        description: self::CTRL.'@create — formulaire de création.',
        summary: 'Films — formulaire de création (Inertia)',
        security: [['sessionCookie' => []]],
        tags: [self::TAG],
        responses: [
            new OA\Response(response: 200, ref: '#/components/responses/InertiaPage'),
            new OA\Response(response: 403, description: 'Policy `create` refusée.'),
        ],
    )]
    public function create(): void {}

    #[OA\Post(
        path: '/dashboard/films',
        operationId: 'adminFilmsStore',
        description: self::CTRL.'@store — validation `StoreFilmRequest` (multipart avec affiche).',
        summary: 'Films — créer',
        security: [['sessionCookie' => []], ['csrfToken' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    required: ['title', 'synopsis', 'duration', 'genre', 'poster'],
                    properties: [
                        new OA\Property(property: 'title', type: 'string', maxLength: 255),
                        new OA\Property(property: 'synopsis', type: 'string', maxLength: 5000),
                        new OA\Property(property: 'duration', type: 'integer', maximum: 600, minimum: 1),
                        new OA\Property(property: 'genre', type: 'string', description: 'Enum `App\\FilmGenre`.'),
                        new OA\Property(property: 'poster', type: 'string', format: 'binary', description: 'jpeg,jpg,png,webp — max 2048 Ko.'),
                    ],
                    type: 'object',
                ),
            ),
        ),
        tags: [self::TAG],
        responses: [
            new OA\Response(response: 302, ref: '#/components/responses/WebRedirect'),
            new OA\Response(response: 403, description: 'Policy ou rôle insuffisant.'),
            new OA\Response(response: 422, description: 'Erreur de validation.', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')),
        ],
    )]
    public function store(): void {}

    #[OA\Get(
        path: '/dashboard/films/{film}/edit',
        operationId: 'adminFilmsEdit',
        description: self::CTRL.'@edit',
        summary: 'Films — formulaire d\'édition (Inertia)',
        security: [['sessionCookie' => []]],
        tags: [self::TAG],
        parameters: [
            new OA\Parameter(name: 'film', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, ref: '#/components/responses/InertiaPage'),
            new OA\Response(response: 404, description: 'Film introuvable.'),
        ],
    )]
    public function edit(): void {}

    #[OA\Patch(
        path: '/dashboard/films/{film}',
        operationId: 'adminFilmsUpdate',
        description: self::CTRL.'@update — `UpdateFilmRequest`.',
        summary: 'Films — mettre à jour',
        security: [['sessionCookie' => []], ['csrfToken' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    required: ['title', 'synopsis', 'duration', 'genre'],
                    properties: [
                        new OA\Property(property: 'title', type: 'string'),
                        new OA\Property(property: 'synopsis', type: 'string'),
                        new OA\Property(property: 'duration', type: 'integer'),
                        new OA\Property(property: 'genre', type: 'string'),
                        new OA\Property(property: 'poster', type: 'string', format: 'binary', nullable: true),
                    ],
                    type: 'object',
                ),
            ),
        ),
        tags: [self::TAG],
        parameters: [
            new OA\Parameter(name: 'film', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 302, ref: '#/components/responses/WebRedirect'),
            new OA\Response(response: 422, description: 'Erreur de validation.', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')),
        ],
    )]
    public function update(): void {}

    #[OA\Delete(
        path: '/dashboard/films/{film}',
        operationId: 'adminFilmsDestroy',
        description: self::CTRL.'@destroy — policy `delete`; impossible si des séances existent.',
        summary: 'Films — supprimer',
        security: [['sessionCookie' => []], ['csrfToken' => []]],
        tags: [self::TAG],
        parameters: [
            new OA\Parameter(name: 'film', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 302, ref: '#/components/responses/WebRedirect'),
            new OA\Response(response: 403, description: 'Policy refusée.'),
        ],
    )]
    public function destroy(): void {}
}
