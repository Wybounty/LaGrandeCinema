<?php

namespace App\Models;

use Database\Factories\TicketFactory;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $reservation_id
 * @property int $place_number
 * @property string $qr_code
 * @property string $status
 * @property Carbon $generated_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['reservation_id', 'place_number', 'qr_code', 'status', 'generated_at'])]
class Ticket extends Model
{
    /** @use HasFactory<TicketFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'generated_at' => 'datetime',
        ];
    }

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    public static function qrPng(string $payload): string
    {
        $writer = new PngWriter;

        return $writer->write(new QrCode($payload))->getString();
    }

    public static function qrDataUri(string $payload): string
    {
        $writer = new PngWriter;

        return $writer->write(new QrCode($payload))->getDataUri();
    }
}
