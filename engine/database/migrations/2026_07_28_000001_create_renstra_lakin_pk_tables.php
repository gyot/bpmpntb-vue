<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $newContentTables = ['renstra', 'lakin', 'perjanjian_kinerja'];
    private array $newKategoriTables = [
        'kategorirenstra' => 'kategorirenstra',
        'kategorilakin' => 'kategorilakin',
        'kategoriperjanjian_kinerja' => 'kategoriperjanjian_kinerja',
    ];

    public function up(): void
    {
        foreach ($this->newContentTables as $table) {
            if (!Schema::hasTable($table)) {
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

        foreach (array_keys($this->newKategoriTables) as $table) {
            if (!Schema::hasTable($table)) {
                Schema::create($table, function (Blueprint $t) {
                    $t->id();
                    $t->string('title');
                    $t->string('slug')->nullable();
                    $t->string('thumbnail')->nullable();
                    $t->integer('status')->default(1);
                    $t->string('images')->nullable();
                    $t->timestamps();
                });
            }
        }
    }

    public function down(): void
    {
        foreach ($this->newContentTables as $table) {
            Schema::dropIfExists($table);
        }
        foreach (array_keys($this->newKategoriTables) as $table) {
            Schema::dropIfExists($table);
        }
    }
};
