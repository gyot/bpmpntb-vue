<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $contentTables = [
        'berita', 'artikel', 'buletin', 'jurnal', 'kliping',
        'pengumuman', 'galeri', 'unduhan', 'profil',
    ];

    public function up(): void
    {
        foreach ($this->contentTables as $table) {
            Schema::create($table, function (Blueprint $t) {
                $t->id();
                $t->unsignedBigInteger('id_kategori')->nullable();
                $t->string('slug')->nullable();
                $t->string('title');
                $t->longText('content')->nullable();
                $t->string('images')->nullable();
                $t->string('thumbnail')->nullable();
                $t->integer('status')->default(1);
                $t->integer('viewer')->default(0);
                $t->string('tags')->nullable();
                $t->string('writer')->nullable();
                $t->date('tanggal')->nullable();
                $t->string('file')->nullable();
                $t->timestamps();
            });
        }
    }

    public function down(): void
    {
        foreach ($this->contentTables as $table) {
            Schema::dropIfExists($table);
        }
    }
};
