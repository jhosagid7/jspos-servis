<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('configurations', function (Blueprint $table) {
            $table->boolean('show_commissions')->default(false)->after('business_name');
            $table->boolean('show_freight')->default(false)->after('show_commissions');
            $table->boolean('show_exchange_diff')->default(false)->after('show_freight');
            $table->boolean('show_drivers')->default(false)->after('show_exchange_diff');
            $table->boolean('show_factories')->default(false)->after('show_drivers');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('configurations', function (Blueprint $table) {
            $table->dropColumn(['show_commissions', 'show_freight', 'show_exchange_diff', 'show_drivers', 'show_factories']);
        });
    }
};
