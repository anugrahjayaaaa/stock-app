<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Stored Stockbit NET bandar_detector per axis (so Net view matches Stockbit
 * exactly without recomputing from gross). ALL axis stored as 'all'.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('broksum_bandar', function (Blueprint $table) {
            $table->id();
            $table->string('stock_code', 12);
            $table->date('date');
            $table->string('market_type', 4);
            $table->string('investor_type', 4);
            $table->json('bandar');
            $table->timestamps();

            $table->unique(['stock_code', 'date', 'market_type', 'investor_type'], 'broksum_bandar_unique');
            $table->index(['stock_code', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('broksum_bandar');
    }
};
