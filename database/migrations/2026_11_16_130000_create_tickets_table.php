<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reservation_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('place_number');
            $table->string('qr_code')->unique();
            $table->string('status');
            $table->timestamp('generated_at');
            $table->timestamps();

            $table->unique(['reservation_id', 'place_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
