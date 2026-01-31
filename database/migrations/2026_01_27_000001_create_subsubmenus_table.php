<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subsubmenus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submenu_id')->constrained('submenus')->onDelete('cascade');
            $table->string('name');
            $table->string('route')->nullable();
            $table->string('section')->nullable()->comment('Identificador para asociar con permisos');
            $table->boolean('is_active')->default(true);
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        // Poblar 'section' con valores predeterminados si viene NULL
        DB::statement('UPDATE subsubmenus SET section = LOWER(REPLACE(COALESCE(route, ""), "/", "")) WHERE section IS NULL');
        DB::statement('UPDATE subsubmenus SET section = LOWER(name) WHERE section = ""');
    }

    public function down(): void
    {
        Schema::dropIfExists('subsubmenus');
    }
};
