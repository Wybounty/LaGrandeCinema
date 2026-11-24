<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

class StripeWebhookEndpoint
{
    #[OA\Post(
        path: '/stripe/webhook',
        operationId: 'stripeWebhook',
        description: 'Endpoint appelé par Stripe lors des événements `checkout.session.*`. Le corps doit être la payload brute signée par Stripe. CSRF désactivé pour cette route.',
        summary: 'Webhook Stripe',
        security: [['stripeSignature' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            description: 'Événement JSON Stripe (payload brut).',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(type: 'object'),
            ),
        ),
        tags: ['Paiements'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Événement accepté.',
                content: new OA\MediaType(
                    mediaType: 'text/plain',
                    schema: new OA\Schema(type: 'string', example: 'OK'),
                ),
            ),
            new OA\Response(
                response: 400,
                description: 'Signature ou payload invalide.',
                content: new OA\MediaType(
                    mediaType: 'text/plain',
                    schema: new OA\Schema(type: 'string', example: 'Invalid signature'),
                ),
            ),
            new OA\Response(
                response: 500,
                description: 'Secret webhook non configuré côté application.',
                content: new OA\MediaType(
                    mediaType: 'text/plain',
                    schema: new OA\Schema(type: 'string', example: 'Webhook secret not configured'),
                ),
            ),
        ],
    )]
    public function webhook(): void
    {
    }
}