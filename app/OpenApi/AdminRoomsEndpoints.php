<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

class AdminRoomsEndpoints
{
    private const TAG = 'Administration – Interface Inertia';

    #[OA\Get(
        path: '/dashboard/rooms',
        operationId: 'adminRoomsIndex',
        description: '`Admin\\RoomController@index` — salles et capacité (`capacity` = places).',
        summary: 'Salles — liste (Inertia)',
        security: [['sessionCookie' => []]],
        tags: [self::TAG],
        responses: [new OA\Response(response: 200, ref: '#/components/responses/InertiaPage')],
    )]
    public function index(): void
    {
    }

    #[OA\Get(
        path: '/dashboard/rooms/create',
        operationId: 'adminRoomsCreate',
        summary: 'Salles — formulaire de création (Inertia)',
        security: [['sessionCookie' => []]],
        tags: [self::TAG],
        responses: [new OA\Response(response: 200, ref: '#/components/responses/InertiaPage')],
    )]
    public function create(): void
    {
    }

    #[OA\Post(
        path: '/dashboard/rooms',
        operationId: 'adminRoomsStore',
        description: '`StoreRoomRequest`',
        summary: 'Salles — créer',
        security: [['sessionCookie' => []], ['csrfToken' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'capacity'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', maxLength: 255),
                    new OA\Property(property: 'capacity', type: 'integer', minimum: 1, maximum: 1000, description: 'Nombre de places dans la salle.'),
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
        path: '/dashboard/rooms/{room}/edit',
        operationId: 'adminRoomsEdit',
        summary: 'Salles — édition (Inertia)',
        security: [['sessionCookie' => []]],
        tags: [self::TAG],
        parameters: [new OA\Parameter(name: 'room', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, ref: '#/components/responses/InertiaPage')],
    )]
    public function edit(): void
    {
    }

    #[OA\Patch(
        path: '/dashboard/rooms/{room}',
        operationId: 'adminRoomsUpdate',
        description: '`UpdateRoomRequest`',
        summary: 'Salles — mettre à jour',
        security: [['sessionCookie' => []], ['csrfToken' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/RoomForm'),
        ),
        tags: [self::TAG],
        parameters: [new OA\Parameter(name: 'room', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 302, ref: '#/components/responses/WebRedirect'),
            new OA\Response(response: 422, description: 'Erreur de validation.', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')),
        ],
    )]
    public function update(): void
    {
    }

    #[OA\Delete(
        path: '/dashboard/rooms/{room}',
        operationId: 'adminRoomsDestroy',
        summary: 'Salles — supprimer',
        security: [['sessionCookie' => []], ['csrfToken' => []]],
        tags: [self::TAG],
        parameters: [new OA\Parameter(name: 'room', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 302, ref: '#/components/responses/WebRedirect')],
    )]
    public function destroy(): void
    {
    }
}