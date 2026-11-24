<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

class AdminTicketControlInertiaEndpoint
{
    #[OA\Get(
        path: '/dashboard/ticket-control',
        operationId: 'adminTicketControlIndex',
        description: <<<'DESC'
            `Admin\\TicketControlController@index` — écran de scan (Inertia).
            Les actions JSON de vérification sont documentées séparément (`Contrôle billets (JSON)`).
            DESC,
        summary: 'Contrôle billets — interface (Inertia)',
        security: [['sessionCookie' => []]],
        tags: ['Administration – Interface Inertia'],
        responses: [
            new OA\Response(response: 200, ref: '#/components/responses/InertiaPage'),
            new OA\Response(response: 403, description: 'Policy `checkAtEntrance` refusée.'),
        ],
    )]
    public function index(): void
    {
    }
}