<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('running_trades', function (Blueprint $table) {
            $table->id();
            $table->string('code', 4);
            $table->date('trade_date');
            $table->unsignedInteger('seq');
            $table->time('time');
            $table->unsignedInteger('price');   // Rupiah per share
            $table->unsignedInteger('lot');     // 1..50000 (0 = open/close marker)
            $table->string('broker', 4)->nullable(); // null for open/close markers
            $table->string('side', 4)->nullable();   // buy|sell, null for markers
            $table->timestamps();

            $table->unique(['code', 'trade_date', 'seq']);
            $table->index(['code', 'trade_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('running_trades');
    }
};
