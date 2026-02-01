<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tareas', function (Blueprint $table) {
            $table->id();
            $table->string('titulo');
            $table->text('descripcion')->nullable();
            $table->enum('estado', ['pendiente', 'en_proceso', 'en_revision', 'finalizado', 'cancelado'])->default('pendiente');
            $table->enum('prioridad', ['baja', 'media', 'alta', 'urgente'])->default('media');
            $table->foreignId('proyecto_id')->constrained('proyectos')->onDelete('cascade');
            $table->foreignId('creador_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('responsable_id')->nullable()->constrained('users')->onDelete('set null');
            
            // Fechas para tracking
            $table->timestamp('fecha_asignacion')->nullable();
            $table->timestamp('fecha_inicio_trabajo')->nullable();
            $table->timestamp('fecha_finalizacion')->nullable();
            
            // Estimación y tiempo real
            $table->integer('horas_estimadas')->nullable();
            $table->integer('horas_reales')->nullable(); // Se calculará automáticamente
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tareas');
    }
};
