<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->string('primary_color', 20)->default('#2563eb')->nullable()->after('map');
            $table->string('secondary_color', 20)->default('#1f2937')->nullable();
            $table->string('accent_color', 20)->default('#f59e0b')->nullable();
            $table->string('background_color', 20)->default('#f9fafb')->nullable();
            $table->string('surface_color', 20)->default('#ffffff')->nullable();
            $table->string('text_primary_color', 20)->default('#1f293b')->nullable();
            $table->string('text_secondary_color', 20)->default('#6b7280')->nullable();
            $table->string('sidebar_bg_color', 20)->default('#1f2937')->nullable();
            $table->string('sidebar_text_color', 20)->default('#e5e7eb')->nullable();
            $table->string('navbar_bg_color', 20)->default('#1e40af')->nullable();
            $table->string('navbar_text_color', 20)->default('#ffffff')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn([
                'primary_color', 'secondary_color', 'accent_color',
                'background_color', 'surface_color',
                'text_primary_color', 'text_secondary_color',
                'sidebar_bg_color', 'sidebar_text_color',
                'navbar_bg_color', 'navbar_text_color',
            ]);
        });
    }
};
