<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

class HealthEndpoint
{
    #[OA\Get(
        path: '/up',
        operationId: 'healthCheck',
        description: 'Contrôle de disponibilité Laravel (health check).',
        summary: 'Santé de l\'application',
        tags: ['Santé'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Application opérationnelle.',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'ok'),
                    ],
                    type: 'object',
                ),
            ),
            new OA\Response(response: 500, description: 'Application indisponible.'),
        ],
    )]
    public function health(): void {}
}
