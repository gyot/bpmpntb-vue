<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_menu_access', function (Blueprint $t) {
            $t->dropForeign(['user_id']);
        });
    }

    public function down(): void
    {
        Schema::table('user_menu_access', function (Blueprint $t) {
            $t->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }
};
