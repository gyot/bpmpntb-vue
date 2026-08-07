<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('user_menu_access')) {
            return;
        }

        $constraints = DB::select(
            <<<'SQL'
                SELECT CONSTRAINT_NAME
                FROM information_schema.KEY_COLUMN_USAGE
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = 'user_menu_access'
                  AND COLUMN_NAME = 'user_id'
                  AND REFERENCED_TABLE_NAME IS NOT NULL
            SQL
        );

        foreach ($constraints as $constraint) {
            $name = str_replace('`', '``', $constraint->CONSTRAINT_NAME);
            DB::statement("ALTER TABLE `user_menu_access` DROP FOREIGN KEY `{$name}`");
        }
    }

    public function down(): void
    {
        // SIAMIN user IDs are external and must not reference the local users table.
    }
};
