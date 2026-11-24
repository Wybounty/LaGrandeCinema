<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

class TicketQrEndpoint
{
    #[OA\Get(
        path: '/tickets/{ticket}/qr',
        operationId: 'ticketQrCode',
        description: 'Retourne l\'image PNG du QR code pour un billet appartenant au spectateur connecté.',
        summary: 'Image QR d\'un billet',
        security: [['sessionCookie' => []]],
        tags: ['Billets'],
        parameters: [
            new OA\Parameter(
                name: 'ticket',
                description: 'Identifiant du billet.',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Image PNG du QR code.',
                content: new OA\MediaType(
                    mediaType: 'image/png',
                    schema: new OA\Schema(type: 'string', format: 'binary'),
                ),
            ),
            new OA\Response(response: 401, description: 'Utilisateur non authentifié.'),
            new OA\Response(response: 403, description: 'Billet d\'un autre utilisateur ou rôle inadapté.'),
            new OA\Response(response: 404, description: 'Billet introuvable.'),
        ],
    )]
    public function qrCode(): void
    {
    }
}