<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wa_broadcast_logs', function (Blueprint $table) {
            $table->json('results')->nullable()->after('total_sent');
        });
    }

    public function down(): void
    {
        Schema::table('wa_broadcast_logs', function (Blueprint $table) {
            $table->dropColumn('results');
        });
    }
};
