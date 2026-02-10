<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DiaFeriado;
use Carbon\Carbon;

class DiasFeriadosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $anioActual = Carbon::now()->year;

        $feriados = [
            // Feriados recurrentes de Bolivia
            [
                'fecha' => Carbon::create($anioActual, 1, 1),
                'nombre' => 'Año Nuevo',
                'descripcion' => 'Celebración del inicio del año',
                'recurrente' => true,
                'activo' => true,
            ],
            [
                'fecha' => Carbon::create($anioActual, 1, 22),
                'nombre' => 'Día del Estado Plurinacional',
                'descripcion' => 'Conmemoración de la fundación del Estado Plurinacional de Bolivia',
                'recurrente' => true,
                'activo' => true,
            ],
            [
                'fecha' => Carbon::create($anioActual, 5, 1),
                'nombre' => 'Día del Trabajo',
                'descripcion' => 'Día Internacional de los Trabajadores',
                'recurrente' => true,
                'activo' => true,
            ],
            [
                'fecha' => Carbon::create($anioActual, 8, 6),
                'nombre' => 'Día de la Independencia',
                'descripcion' => 'Independencia de Bolivia',
                'recurrente' => true,
                'activo' => true,
            ],
            [
                'fecha' => Carbon::create($anioActual, 11, 2),
                'nombre' => 'Día de Todos los Santos',
                'descripcion' => 'Conmemoración de los difuntos',
                'recurrente' => true,
                'activo' => true,
            ],
            [
                'fecha' => Carbon::create($anioActual, 12, 25),
                'nombre' => 'Navidad',
                'descripcion' => 'Celebración del nacimiento de Jesús',
                'recurrente' => true,
                'activo' => true,
            ],
        ];

        foreach ($feriados as $feriado) {
            DiaFeriado::updateOrCreate(
                [
                    'fecha' => $feriado['fecha']->format('Y-m-d'),
                ],
                $feriado
            );
        }

        $this->command->info('Feriados de Bolivia creados exitosamente para el año ' . $anioActual);
    }
}
