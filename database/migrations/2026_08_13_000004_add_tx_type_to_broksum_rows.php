<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Add tx_type so gross + net crawls coexist in broksum_rows. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('broksum_rows', function (Blueprint $table) {
            $table->string('tx_type', 8)->default('gross')->after('investor_type');
            $table->index(['stock_code', 'date', 'tx_type']);
        });
    }

    public function down(): void
    {
        Schema::table('broksum_rows', function (Blueprint $table) {
            $table->dropColumn('tx_type');
        });
    }
};
