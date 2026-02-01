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
            // Eliminar campos de horas
            $table->dropColumn(['horas_estimadas', 'horas_reales']);
            
            // Eliminar campos de fechas antiguas
            $table->dropColumn(['fecha_asignacion', 'fecha_inicio_trabajo', 'fecha_finalizacion']);
            
            // Agregar solo fecha_inicio y fecha_fin (con hora)
            $table->timestamp('fecha_inicio')->nullable()->after('responsable_id');
            $table->timestamp('fecha_fin')->nullable()->after('fecha_inicio');
            
            // Agregar campos de auditoría
            $table->foreignId('created_by')->nullable()->after('updated_at')->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tareas', function (Blueprint $table) {
            // Revertir campos de auditoría
            $table->dropForeign(['created_by']);
            $table->dropForeign(['updated_by']);
            $table->dropColumn(['created_by', 'updated_by']);
            
            // Revertir fechas
            $table->dropColumn(['fecha_inicio', 'fecha_fin']);
            
            // Restaurar campos antiguos
            $table->timestamp('fecha_asignacion')->nullable();
            $table->timestamp('fecha_inicio_trabajo')->nullable();
            $table->timestamp('fecha_finalizacion')->nullable();
            $table->decimal('horas_estimadas', 8, 2)->nullable();
            $table->decimal('horas_reales', 8, 2)->nullable();
        });
    }
};
