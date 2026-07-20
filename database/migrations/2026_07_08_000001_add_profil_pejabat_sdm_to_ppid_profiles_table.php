<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ppid_profiles', function (Blueprint $t) {
            $t->text('profil_pejabat')->nullable()->after('kontak');
            $t->text('profil_sdm')->nullable()->after('profil_pejabat');
        });
    }

    public function down(): void
    {
        Schema::table('ppid_profiles', function (Blueprint $t) {
            $t->dropColumn(['profil_pejabat', 'profil_sdm']);
        });
    }
};
