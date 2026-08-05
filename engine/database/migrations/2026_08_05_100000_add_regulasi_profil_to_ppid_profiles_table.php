<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ppid_profiles', function (Blueprint $t) {
            $t->text('regulasi_profil')->nullable()->after('permohonan_phone');
        });
    }

    public function down(): void
    {
        Schema::table('ppid_profiles', function (Blueprint $t) {
            $t->dropColumn('regulasi_profil');
        });
    }
};
