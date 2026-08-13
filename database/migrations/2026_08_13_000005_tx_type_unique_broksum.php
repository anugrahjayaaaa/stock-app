<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Unique key must include tx_type (gross + net coexist per axis). */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('broksum_rows', function (Blueprint $table) {
            $table->dropUnique('broksum_unique');
            $table->unique(
                ['stock_code', 'date', 'tx_type', 'market_type', 'investor_type', 'broker_code', 'side'],
                'broksum_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('broksum_rows', function (Blueprint $table) {
            $table->dropUnique('broksum_unique');
            $table->unique(
                ['stock_code', 'date', 'market_type', 'investor_type', 'broker_code', 'side'],
                'broksum_unique'
            );
        });
    }
};
