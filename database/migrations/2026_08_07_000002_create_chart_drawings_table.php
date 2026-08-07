<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;

return new class extends Migration
{
    public function up(): void
    {
        // ponytail: one row per user+symbol; drawing_json holds the full
        // DrawingManager serialization. Swap to delta rows later if needed.
        Schema::create('chart_drawings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('symbol');
            $table->json('drawing_json');
            $table->timestamps();

            $table->unique(['user_id', 'symbol']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chart_drawings');
    }
};
