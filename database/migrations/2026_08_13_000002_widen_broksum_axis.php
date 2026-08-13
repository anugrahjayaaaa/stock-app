<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Widen axis columns to hold the 'all' sentinel (Stockbit server-side ALL view). */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('broksum_rows', function (Blueprint $table) {
            $table->string('market_type', 4)->change();
            $table->string('investor_type', 4)->change();
        });
    }

    public function down(): void
    {
        Schema::table('broksum_rows', function (Blueprint $table) {
            $table->string('market_type', 4)->change();
            $table->string('investor_type', 2)->change();
        });
    }
};
