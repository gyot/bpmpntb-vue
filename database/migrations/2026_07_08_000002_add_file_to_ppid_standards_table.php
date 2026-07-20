<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ppid_standards', function (Blueprint $t) {
            $t->string('file')->nullable()->after('maklumat');
        });
    }

    public function down(): void
    {
        Schema::table('ppid_standards', function (Blueprint $t) {
            $t->dropColumn('file');
        });
    }
};
