<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

class AdminReservationsEndpoints
{
    private const TAG = 'Administration – Interface Inertia';

    #[OA\Get(
        path: '/dashboard/reservations',
        operationId: 'adminReservationsIndex',
        description: '`Admin\\ReservationController@index` — policy `viewAny` sur `Reservation`.',
        summary: 'Réservations — liste (Inertia)',
        security: [['sessionCookie' => []]],
        tags: [self::TAG],
        responses: [new OA\Response(response: 200, ref: '#/components/responses/InertiaPage')],
    )]
    public function index(): void {}

    #[OA\Get(
        path: '/dashboard/reservations/{reservation}',
        operationId: 'adminReservationsShow',
        description: '`Admin\\ReservationController@show` — détail avec places, billets, statuts.',
        summary: 'Réservations — détail (Inertia)',
        security: [['sessionCookie' => []]],
        tags: [self::TAG],
        parameters: [
            new OA\Parameter(name: 'reservation', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, ref: '#/components/responses/InertiaPage'),
            new OA\Response(response: 403, description: 'Policy `view` refusée.'),
        ],
    )]
    public function show(): void {}

    #[OA\Post(
        path: '/dashboard/reservations/{reservation}/cancel',
        operationId: 'adminReservationsCancel',
        description: '`Admin\\ReservationController@cancel` — policy `cancel`. Pas de corps JSON ; action POST Inertia.',
        summary: 'Réservations — annuler (redirection)',
        security: [['sessionCookie' => []], ['csrfToken' => []]],
        tags: [self::TAG],
        parameters: [
            new OA\Parameter(name: 'reservation', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 302, ref: '#/components/responses/WebRedirect'),
            new OA\Response(response: 403, description: 'Annulation non autorisée.'),
        ],
    )]
    public function cancel(): void {}
}
