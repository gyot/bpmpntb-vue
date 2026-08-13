<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('layanans', function (Blueprint $table) {
            $table->string('jenis_layanan', 30)->default('layanan_tambahan')->after('title');
            $table->json('requirements')->nullable()->after('content');
            $table->json('flow')->nullable()->after('requirements');
            $table->json('information')->nullable()->after('flow');
            $table->string('file_pendukung')->nullable()->after('pos_file');
            $table->string('url_permohonan', 1000)->nullable()->after('file_pendukung');
        });
    }

    public function down(): void
    {
        Schema::table('layanans', function (Blueprint $table) {
            $table->dropColumn(['jenis_layanan', 'requirements', 'flow', 'information', 'file_pendukung', 'url_permohonan']);
        });
    }
};
