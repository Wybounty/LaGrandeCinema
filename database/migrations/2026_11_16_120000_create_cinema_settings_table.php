<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cinema_settings', function (Blueprint $table) {
            $table->id();
            $table->decimal('ticket_price', 8, 2)->default(12.50);
            $table->timestamps();
        });

        DB::table('cinema_settings')->insert([
            'ticket_price' => 12.50,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('cinema_settings');
    }
};
