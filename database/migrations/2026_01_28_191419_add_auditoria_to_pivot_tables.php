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
        $tables = ['permission_role', 'role_user'];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                // Eliminar created_at y updated_at
                $table->dropColumn(['created_at', 'updated_at']);

                // Agregar campos de auditoría en español
                $table->timestamp('creado_en')->nullable()->comment('Fecha y hora de creación');
                $table->unsignedBigInteger('creado_por')->nullable()->comment('ID del usuario que creó');
                $table->timestamp('modificado_en')->nullable()->comment('Fecha y hora de modificación');
                $table->unsignedBigInteger('modificado_por')->nullable()->comment('ID del usuario que modificó');

                // Agregar llaves foráneas
                $table->foreign('creado_por')->references('id')->on('users')->onDelete('set null');
                $table->foreign('modificado_por')->references('id')->on('users')->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = ['permission_role', 'role_user'];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                // Eliminar llaves foráneas
                $table->dropForeign(['creado_por']);
                $table->dropForeign(['modificado_por']);

                // Eliminar campos de auditoría
                $table->dropColumn(['creado_en', 'creado_por', 'modificado_en', 'modificado_por']);

                // Restaurar timestamps nativos
                $table->timestamps();
            });
        }
    }
};
