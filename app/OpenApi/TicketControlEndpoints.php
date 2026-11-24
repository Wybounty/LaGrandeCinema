<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

class TicketControlEndpoints
{
    #[OA\Post(
        path: '/dashboard/ticket-control/verify',
        operationId: 'ticketControlVerify',
        description: 'Vérifie un billet à partir de son QR code sans le marquer comme utilisé. Réservé aux utilisateurs autorisés (`administrateur` / `super_administrateur`).',
        summary: 'Vérifier un billet (scan)',
        security: [['sessionCookie' => []], ['csrfToken' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/TicketControlVerifyRequest'),
        ),
        tags: ['Contrôle billets (JSON)'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Résultat de la vérification (toujours HTTP 200 pour un QR analysable).',
                content: new OA\JsonContent(ref: '#/components/schemas/TicketControlCheckResponse'),
            ),
            new OA\Response(response: 401, description: 'Utilisateur non authentifié.'),
            new OA\Response(response: 403, description: 'Spectateur ou rôle non autorisé.'),
            new OA\Response(
                response: 422,
                description: 'Erreur de validation (`qr_code` requis, 255 caractères max).',
                content: new OA\JsonContent(ref: '#/components/schemas/ValidationError'),
            ),
        ],
    )]
    public function verify(): void {}

    #[OA\Post(
        path: '/dashboard/ticket-control/mark-used',
        operationId: 'ticketControlMarkUsed',
        description: 'Confirme l\'entrée et passe le billet au statut `used` lorsque la réservation est confirmée et le billet valide.',
        summary: 'Marquer un billet comme utilisé',
        security: [['sessionCookie' => []], ['csrfToken' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/TicketControlVerifyRequest'),
        ),
        tags: ['Contrôle billets (JSON)'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Entrée confirmée ou refus documenté dans le corps JSON.',
                content: new OA\JsonContent(ref: '#/components/schemas/TicketControlCheckResponse'),
            ),
            new OA\Response(response: 401, description: 'Utilisateur non authentifié.'),
            new OA\Response(response: 403, description: 'Spectateur ou rôle non autorisé.'),
            new OA\Response(
                response: 422,
                description: 'Erreur de validation.',
                content: new OA\JsonContent(ref: '#/components/schemas/ValidationError'),
            ),
        ],
    )]
    public function markUsed(): void {}
}
