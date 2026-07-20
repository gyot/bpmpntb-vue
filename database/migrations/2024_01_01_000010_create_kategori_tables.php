<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $kategoriTables = [
        'kategori_berita', 'kategori_artikel', 'kategori_buletin',
        'kategori_jurnal', 'kategori_kliping', 'kategori_pengumuman',
        'kategori_galeri', 'kategori_unduhan', 'kategori_profil',
    ];

    public function up(): void
    {
        foreach ($this->kategoriTables as $table) {
            Schema::create($table, function (Blueprint $t) {
                $t->id();
                $t->string('title');
                $t->string('slug')->nullable();
                $t->string('thumbnail')->nullable();
                $t->integer('status')->default(1);
                $t->timestamps();
            });
        }
    }

    public function down(): void
    {
        foreach ($this->kategoriTables as $table) {
            Schema::dropIfExists($table);
        }
    }
};
