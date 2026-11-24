<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

class AdminSessionsEndpoints
{
    private const TAG = 'Administration – Interface Inertia';

    private const CTRL = '`Admin\\CinemaSessionController`';

    #[OA\Get(
        path: '/dashboard/sessions',
        operationId: 'adminSessionsIndex',
        description: self::CTRL.'@index',
        summary: 'Séances — liste (Inertia)',
        security: [['sessionCookie' => []]],
        tags: [self::TAG],
        responses: [new OA\Response(response: 200, ref: '#/components/responses/InertiaPage')],
    )]
    public function index(): void
    {
    }

    #[OA\Get(
        path: '/dashboard/sessions/create',
        operationId: 'adminSessionsCreate',
        description: self::CTRL.'@create',
        summary: 'Séances — formulaire de création (Inertia)',
        security: [['sessionCookie' => []]],
        tags: [self::TAG],
        responses: [new OA\Response(response: 200, ref: '#/components/responses/InertiaPage')],
    )]
    public function create(): void
    {
    }

    #[OA\Post(
        path: '/dashboard/sessions',
        operationId: 'adminSessionsStore',
        description: self::CTRL.'@store — `CinemaSessionRequest` (+ contraintes d\'horaires via `ValidatesCinemaSessionSchedule`).',
        summary: 'Séances — créer',
        security: [['sessionCookie' => []], ['csrfToken' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['film_id', 'room_id', 'session_date', 'start_time', 'end_time', 'starts_at', 'ends_at'],
                properties: [
                    new OA\Property(property: 'film_id', type: 'integer', description: 'exists:films,id'),
                    new OA\Property(property: 'room_id', type: 'integer', description: 'exists:rooms,id'),
                    new OA\Property(property: 'session_date', type: 'string', format: 'date'),
                    new OA\Property(property: 'start_time', type: 'string', example: '19:30', description: 'format H:i'),
                    new OA\Property(property: 'end_time', type: 'string', example: '21:45'),
                    new OA\Property(property: 'starts_at', type: 'string', format: 'date-time'),
                    new OA\Property(property: 'ends_at', type: 'string', format: 'date-time', description: 'after:starts_at'),
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
        path: '/dashboard/sessions/{cinemaSession}/edit',
        operationId: 'adminSessionsEdit',
        summary: 'Séances — édition (Inertia)',
        security: [['sessionCookie' => []]],
        tags: [self::TAG],
        parameters: [
            new OA\Parameter(name: 'cinemaSession', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [new OA\Response(response: 200, ref: '#/components/responses/InertiaPage')],
    )]
    public function edit(): void
    {
    }

    #[OA\Patch(
        path: '/dashboard/sessions/{cinemaSession}',
        operationId: 'adminSessionsUpdate',
        summary: 'Séances — mettre à jour',
        security: [['sessionCookie' => []], ['csrfToken' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/CinemaSessionForm'),
        ),
        tags: [self::TAG],
        parameters: [
            new OA\Parameter(name: 'cinemaSession', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 302, ref: '#/components/responses/WebRedirect'),
            new OA\Response(response: 422, description: 'Erreur de validation.', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')),
        ],
    )]
    public function update(): void
    {
    }

    #[OA\Delete(
        path: '/dashboard/sessions/{cinemaSession}',
        operationId: 'adminSessionsDestroy',
        description: self::CTRL.'@destroy',
        summary: 'Séances — supprimer',
        security: [['sessionCookie' => []], ['csrfToken' => []]],
        tags: [self::TAG],
        parameters: [
            new OA\Parameter(name: 'cinemaSession', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [new OA\Response(response: 302, ref: '#/components/responses/WebRedirect')],
    )]
    public function destroy(): void
    {
    }
}