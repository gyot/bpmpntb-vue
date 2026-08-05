<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $t) {
            $t->string('silamo_title')->nullable()->after('ikm_link');
            $t->string('silamo_subtitle')->nullable()->after('silamo_title');
            $t->string('silamo_schedule')->nullable()->after('silamo_subtitle');
            $t->string('silamo_meeting_id')->nullable()->after('silamo_schedule');
            $t->string('silamo_password')->nullable()->after('silamo_meeting_id');
            $t->string('silamo_link')->nullable()->after('silamo_password');
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $t) {
            $t->dropColumn(['silamo_title','silamo_subtitle','silamo_schedule','silamo_meeting_id','silamo_password','silamo_link']);
        });
    }
};
