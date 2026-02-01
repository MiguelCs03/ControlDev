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
        Schema::table('tareas', function (Blueprint $table) {
            // Eliminar los campos de Laravel que no quieres
            $table->dropForeign(['created_by']);
            $table->dropForeign(['updated_by']);
            $table->dropColumn(['created_by', 'updated_by']);
            
            // Desactivar timestamps automáticos agregando estas columnas manualmente
            $table->dropTimestamps();
            
            // Agregar campos personalizados en español
            $table->string('creado_por')->nullable()->after('responsable_id');
            $table->timestamp('creado_en')->nullable()->after('creado_por');
            $table->string('modificado_por')->nullable()->after('creado_en');
            $table->timestamp('modificado_en')->nullable()->after('modificado_por');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tareas', function (Blueprint $table) {
            // Revertir campos personalizados
            $table->dropColumn(['creado_por', 'creado_en', 'modificado_por', 'modificado_en']);
            
            // Restaurar timestamps de Laravel
            $table->timestamps();
            
            // Restaurar created_by y updated_by
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
        });
    }
};
