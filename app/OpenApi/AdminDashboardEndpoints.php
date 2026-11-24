<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

class AdminDashboardEndpoints
{
    #[OA\Get(
        path: '/dashboard',
        operationId: 'dashboardIndex',
        description: <<<'DESC'
            Tableau de bord connecté. **Contrôleur** : `DashboardController@index`.
            Si l'utilisateur peut gérer les utilisateurs (`canManageUsers`), les statistiques sont calculées par `Admin\StatisticsController::collect()` et passées en prop Inertia `statistics`.
            Spectateurs : page sans bloc statistiques admin.
            DESC,
        summary: 'Tableau de bord et statistiques (Inertia)',
        security: [['sessionCookie' => []]],
        tags: ['Tableau de bord – Interface Inertia'],
        parameters: [
            new OA\Parameter(
                name: 'period',
                description: 'Période d\'agrégation (`App\StatisticsPeriod`). Valeurs : `today`, `week`, `month`, `year`. Défaut applicatif : `month`.',
                in: 'query',
                required: false,
                schema: new OA\Schema(
                    type: 'string',
                    default: 'month',
                    enum: ['today', 'week', 'month', 'year'],
                ),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                ref: '#/components/responses/InertiaPage',
            ),
            new OA\Response(response: 401, ref: '#/components/responses/WebRedirectToLogin'),
            new OA\Response(response: 403, description: 'Compte non vérifié (`verified`).'),
        ],
    )]
    public function index(): void {}
}
