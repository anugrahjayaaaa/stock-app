<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Gross broksum rows — grain: stock x date x market x investor x broker x side.
 * ALL market/investor is derived (SUM), never stored.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('broksum_rows', function (Blueprint $table) {
            $table->id();
            $table->string('stock_code', 12);
            $table->date('date');
            $table->string('market_type', 4);   // rg | tn | ng
            $table->string('investor_type', 2); // f  | d
            $table->string('broker_code', 16);
            $table->string('side', 4);          // buy | sell
            $table->bigInteger('lot')->default(0);
            $table->bigInteger('val')->default(0); // rupiah
            $table->decimal('avg', 12, 4)->nullable();
            $table->integer('freq')->default(0);
            $table->timestamps();

            $table->unique(['stock_code', 'date', 'market_type', 'investor_type', 'broker_code', 'side'], 'broksum_unique');
            $table->index(['stock_code', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('broksum_rows');
    }
};
