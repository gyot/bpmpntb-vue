<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ppid_profiles', function (Blueprint $t) {
            $t->string('beranda_image')->nullable()->after('profil_sdm');
            $t->string('beranda_title')->nullable()->after('beranda_image');
            $t->text('beranda_description')->nullable()->after('beranda_title');
            $t->text('navigations')->nullable()->after('beranda_description');
            $t->string('permohonan_link')->nullable()->after('navigations');
            $t->string('permohonan_email')->nullable()->after('permohonan_link');
            $t->string('permohonan_phone')->nullable()->after('permohonan_email');
        });

        Schema::create('ppid_external_links', function (Blueprint $t) {
            $t->id();
            $t->string('title');
            $t->string('link')->nullable();
            $t->string('image')->nullable();
            $t->integer('order')->default(0);
            $t->integer('status')->default(1);
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ppid_external_links');
        Schema::table('ppid_profiles', function (Blueprint $t) {
            $t->dropColumn(['beranda_image','beranda_title','beranda_description','navigations','permohonan_link','permohonan_email','permohonan_phone']);
        });
    }
};
