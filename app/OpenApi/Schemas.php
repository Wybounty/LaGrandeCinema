<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'TicketControlVerifyRequest',
    required: ['qr_code'],
    properties: [
        new OA\Property(
            property: 'qr_code',
            type: 'string',
            maxLength: 255,
            example: '550e8400-e29b-41d4-a716-446655440000',
            description: 'Code QR du billet (UUID ou identifiant stocké en base).',
        ),
    ],
    type: 'object',
)]
#[OA\Schema(
    schema: 'TicketPayload',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'qr_code', type: 'string', example: '550e8400-e29b-41d4-a716-446655440000'),
        new OA\Property(property: 'place_number', type: 'integer', example: 12),
        new OA\Property(property: 'status', type: 'string', enum: ['valid', 'used', 'cancelled']),
        new OA\Property(property: 'film_title', type: 'string', example: 'Le Voyage extraordinaire'),
        new OA\Property(
            property: 'session_starts_at',
            type: 'string',
            format: 'date-time',
            example: '2026-11-20T19:30:00-04:00',
            nullable: true,
        ),
        new OA\Property(
            property: 'session_ends_at',
            type: 'string',
            format: 'date-time',
            example: '2026-11-20T21:45:00-04:00',
            nullable: true,
        ),
        new OA\Property(property: 'room_name', type: 'string', example: 'Salle 1'),
    ],
    type: 'object',
)]
#[OA\Schema(
    schema: 'TicketControlCheckResponse',
    required: ['outcome', 'message'],
    properties: [
        new OA\Property(
            property: 'outcome',
            type: 'string',
            enum: ['valid', 'invalid', 'cancelled', 'used'],
            example: 'valid',
        ),
        new OA\Property(property: 'message', type: 'string', example: 'Billet valide. Vous pouvez confirmer l\'entrée.'),
        new OA\Property(
            property: 'ticket',
            ref: '#/components/schemas/TicketPayload',
            nullable: true,
        ),
        new OA\Property(
            property: 'success',
            type: 'boolean',
            example: true,
            description: 'Présent uniquement lors de la confirmation d\'entrée réussie (`mark-used`).',
        ),
    ],
    type: 'object',
)]
#[OA\Schema(
    schema: 'ValidationError',
    properties: [
        new OA\Property(property: 'message', type: 'string', example: 'The qr code field is required.'),
        new OA\Property(
            property: 'errors',
            type: 'object',
            example: ['qr_code' => ['The qr code field is required.']],
            additionalProperties: new OA\AdditionalProperties(
                type: 'array',
                items: new OA\Items(type: 'string'),
            ),
        ),
    ],
    type: 'object',
)]
#[OA\Schema(
    schema: 'RoomForm',
    required: ['name', 'capacity'],
    properties: [
        new OA\Property(property: 'name', type: 'string', maxLength: 255),
        new OA\Property(property: 'capacity', type: 'integer', minimum: 1, maximum: 1000),
    ],
    type: 'object',
)]
#[OA\Schema(
    schema: 'CinemaSessionForm',
    required: ['film_id', 'room_id', 'session_date', 'start_time', 'end_time', 'starts_at', 'ends_at'],
    properties: [
        new OA\Property(property: 'film_id', type: 'integer'),
        new OA\Property(property: 'room_id', type: 'integer'),
        new OA\Property(property: 'session_date', type: 'string', format: 'date'),
        new OA\Property(property: 'start_time', type: 'string', example: '19:30'),
        new OA\Property(property: 'end_time', type: 'string', example: '21:45'),
        new OA\Property(property: 'starts_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'ends_at', type: 'string', format: 'date-time'),
    ],
    type: 'object',
)]
class Schemas {}
