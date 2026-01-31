<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $tables = ['users', 'menus', 'submenus', 'subsubmenus', 'roles', 'permissions'];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                // Eliminar created_at y updated_at si existen
                if (Schema::hasColumn($table->getTable(), 'created_at')) {
                    $table->dropColumn('created_at');
                }
                if (Schema::hasColumn($table->getTable(), 'updated_at')) {
                    $table->dropColumn('updated_at');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = ['users', 'menus', 'submenus', 'subsubmenus', 'roles', 'permissions'];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->timestamps();
            });
        }
    }
};
