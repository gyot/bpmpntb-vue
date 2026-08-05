<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_menu_access', function (Blueprint $table) {
            if (!Schema::hasColumn('user_menu_access', 'sub_menu_key')) {
                $table->string('sub_menu_key', 100)->nullable()->after('menu_key');
            }
        });

        $indexes = collect(DB::select("SHOW INDEX FROM user_menu_access"))->pluck('Key_name')->toArray();

        if (!in_array('user_menu_access_user_id_index', $indexes)) {
            DB::statement('ALTER TABLE `user_menu_access` ADD INDEX `user_menu_access_user_id_index` (`user_id`)');
        }

        if (!in_array('user_menu_access_user_id_menu_key_sub_menu_key_unique', $indexes)) {
            DB::statement('ALTER TABLE `user_menu_access` ADD UNIQUE `user_menu_access_user_id_menu_key_sub_menu_key_unique` (`user_id`, `menu_key`, `sub_menu_key`)');
        }

        if (in_array('user_menu_access_user_id_menu_key_unique', $indexes)) {
            DB::statement('ALTER TABLE `user_menu_access` DROP INDEX `user_menu_access_user_id_menu_key_unique`');
        }
    }

    public function down(): void
    {
        Schema::table('user_menu_access', function (Blueprint $table) {
            $table->unique(['user_id', 'menu_key']);
            $table->dropUnique(['user_id', 'menu_key', 'sub_menu_key']);
            $table->dropColumn('sub_menu_key');
        });
    }
};
