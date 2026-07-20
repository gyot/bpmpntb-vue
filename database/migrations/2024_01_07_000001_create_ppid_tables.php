<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create("ppid_profiles", function (Blueprint $t) {
            $t->id();
            $t->string("title")->default("Profil PPID BPMP Provinsi NTB");
            $t->text("about")->nullable();
            $t->text("visi")->nullable();
            $t->text("misi")->nullable();
            $t->text("tupoksi")->nullable();
            $t->string("struktur_image")->nullable();
            $t->text("kontak")->nullable();
            $t->timestamps();
        });

        Schema::create("ppid_informations", function (Blueprint $t) {
            $t->id();
            $t->string("category")->default("berkala");
            $t->string("title");
            $t->text("description")->nullable();
            $t->string("file")->nullable();
            $t->string("link")->nullable();
            $t->integer("order")->default(0);
            $t->integer("status")->default(1);
            $t->timestamps();
        });

        Schema::create("ppid_standards", function (Blueprint $t) {
            $t->id();
            $t->string("title");
            $t->text("content")->nullable();
            $t->text("prosedur")->nullable();
            $t->text("maklumat")->nullable();
            $t->integer("order")->default(0);
            $t->integer("status")->default(1);
            $t->timestamps();
        });

        Schema::create("ppid_regulations", function (Blueprint $t) {
            $t->id();
            $t->string("title");
            $t->string("nomor")->nullable();
            $t->text("description")->nullable();
            $t->string("file")->nullable();
            $t->string("link")->nullable();
            $t->date("tanggal")->nullable();
            $t->integer("order")->default(0);
            $t->integer("status")->default(1);
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("ppid_regulations");
        Schema::dropIfExists("ppid_standards");
        Schema::dropIfExists("ppid_informations");
        Schema::dropIfExists("ppid_profiles");
    }
};