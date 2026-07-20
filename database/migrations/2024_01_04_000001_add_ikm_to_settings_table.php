<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->decimal('ikm_score', 5, 2)->default(0)->nullable()->after('map');
            $table->string('ikm_period')->default('Triwulan I Tahun 2026')->nullable()->after('ikm_score');
            $table->string('ikm_link')->nullable()->after('ikm_period');
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn(['ikm_score', 'ikm_period', 'ikm_link']);
        });
    }
};
