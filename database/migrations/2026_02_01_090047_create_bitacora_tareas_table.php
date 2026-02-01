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
        Schema::create('bitacora_tareas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tarea_id')->constrained('tareas')->onDelete('cascade');
            $table->foreignId('usuario_id')->nullable()->constrained('users')->nullOnDelete();
            
            // Tipo de acción: creada, asignada, iniciada, pausada, reanudada, enviada_revision, finalizada, cancelada, comentada
            $table->string('accion', 50);
            
            // Descripción legible: "Miguel Angel se asignó la tarea de Diseñar base de datos"
            $table->text('descripcion');
            
            // Datos adicionales en JSON por si se necesitan (opcional)
            $table->json('metadata')->nullable();
            
            // Campo personalizado en lugar de timestamps
            $table->timestamp('creado_en')->nullable();
            
            // Índices para consultas rápidas
            $table->index('tarea_id');
            $table->index('usuario_id');
            $table->index('accion');
            $table->index('creado_en');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bitacora_tareas');
    }
};
