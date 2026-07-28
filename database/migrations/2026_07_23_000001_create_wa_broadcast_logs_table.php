<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wa_broadcast_logs', function (Blueprint $table) {
            $table->id();
            $table->text('message');
            $table->integer('total_numbers');
            $table->integer('total_sent');
            $table->unsignedBigInteger('admin_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wa_broadcast_logs');
    }
};
