<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chatbot_responses', function (Blueprint $table) {
            $table->id();
            $table->string('keyword');
            $table->text('response');
            $table->string('category')->nullable();
            $table->integer('order')->default(0);
            $table->integer('status')->default(1);
            $table->timestamps();
        });

        Schema::create('chatbot_logs', function (Blueprint $table) {
            $table->id();
            $table->string('session_id');
            $table->string('nama')->nullable();
            $table->string('instansi')->nullable();
            $table->string('kontak')->nullable();
            $table->text('message');
            $table->enum('sender', ['user', 'bot']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chatbot_responses');
        Schema::dropIfExists('chatbot_logs');
    }
};
