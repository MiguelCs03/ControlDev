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
        Schema::create('dias_feriados', function (Blueprint $table) {
            $table->id();
            $table->date('fecha');
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->boolean('recurrente')->default(false); // Si se repite cada año
            $table->boolean('activo')->default(true);
            $table->timestamps();
            
            // Índices para búsqueda rápida
            $table->index('fecha');
            $table->index('activo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dias_feriados');
    }
};
