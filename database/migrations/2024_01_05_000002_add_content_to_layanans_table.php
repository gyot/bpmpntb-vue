<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('layanans', function (Blueprint $table) {
            $table->text('content')->nullable()->after('image');
            $table->string('slug')->nullable()->after('content');
            $table->string('tags')->nullable()->after('slug');
            $table->string('writer')->nullable()->after('tags');
            $table->date('tanggal')->nullable()->after('writer');
        });
    }

    public function down(): void
    {
        Schema::table('layanans', function (Blueprint $table) {
            $table->dropColumn(['content', 'slug', 'tags', 'writer', 'tanggal']);
        });
    }
};
