<?php

namespace App\Http\Controllers;

use App\Models\DiaFeriado;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DiaFeriadoController extends Controller
{
    /**
     * Listar todos los días feriados
     */
    public function index(Request $request)
    {
        $query = DiaFeriado::query();
        
        // Filtrar por año si se especifica
        if ($request->has('anio')) {
            $query->whereYear('fecha', $request->anio);
        }
        
        // Filtrar por activos
        if ($request->has('activo')) {
            $query->where('activo', $request->activo);
        }
        
        $feriados = $query->orderBy('fecha', 'asc')->get();
        
        return response()->json($feriados);
    }

    /**
     * Crear un nuevo día feriado
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'fecha' => 'required|date',
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'recurrente' => 'boolean',
            'activo' => 'boolean',
        ]);

        $feriado = DiaFeriado::create($validated);

        return response()->json([
            'message' => 'Día feriado creado exitosamente',
            'feriado' => $feriado
        ], 201);
    }

    /**
     * Mostrar un día feriado específico
     */
    public function show(DiaFeriado $feriado)
    {
        return response()->json($feriado);
    }

    /**
     * Actualizar un día feriado
     */
    public function update(Request $request, DiaFeriado $feriado)
    {
        $validated = $request->validate([
            'fecha' => 'sometimes|required|date',
            'nombre' => 'sometimes|required|string|max:255',
            'descripcion' => 'nullable|string',
            'recurrente' => 'boolean',
            'activo' => 'boolean',
        ]);

        $feriado->update($validated);

        return response()->json([
            'message' => 'Día feriado actualizado exitosamente',
            'feriado' => $feriado
        ]);
    }

    /**
     * Eliminar un día feriado
     */
    public function destroy($id)
    {
        \Log::info('Intentando eliminar feriado', ['id' => $id]);
        
        // Buscar el feriado primero
        $feriado = DiaFeriado::find($id);
        
        if (!$feriado) {
            \Log::error('Feriado no encontrado', ['id' => $id]);
            return response()->json([
                'message' => 'Feriado no encontrado'
            ], 404);
        }
        
        \Log::info('Feriado encontrado', [
            'id' => $feriado->id,
            'nombre' => $feriado->nombre,
            'fecha' => $feriado->fecha
        ]);
        
        // Eliminar usando query builder para asegurar eliminación física
        $deleted = \DB::table('dias_feriados')->where('id', $id)->delete();
        
        \Log::info('Resultado de eliminación', [
            'id' => $id,
            'rows_deleted' => $deleted
        ]);

        return response()->json([
            'message' => 'Día feriado eliminado exitosamente',
            'deleted' => $deleted
        ]);
    }

    /**
     * Obtener días no laborables de un mes específico
     */
    public function diasNoLaborables(Request $request)
    {
        $mes = $request->input('mes', now()->month);
        $anio = $request->input('anio', now()->year);

        $diasNoLaborables = DiaFeriado::getDiasNoLaborables($mes, $anio);

        return response()->json([
            'mes' => $mes,
            'anio' => $anio,
            'dias_no_laborables' => $diasNoLaborables,
            'total' => count($diasNoLaborables)
        ]);
    }

    /**
     * Verificar si una fecha es laborable
     */
    public function verificarDiaLaborable(Request $request)
    {
        $fecha = $request->input('fecha');
        
        if (!$fecha) {
            return response()->json([
                'error' => 'Fecha requerida'
            ], 400);
        }

        $esLaborable = DiaFeriado::esDiaLaborable($fecha);
        $esFeriado = DiaFeriado::esFeriado($fecha);
        $fechaCarbon = Carbon::parse($fecha);
        $esFinDeSemana = $fechaCarbon->isWeekend();

        return response()->json([
            'fecha' => $fecha,
            'es_laborable' => $esLaborable,
            'es_feriado' => $esFeriado,
            'es_fin_de_semana' => $esFinDeSemana,
            'dia_semana' => $fechaCarbon->locale('es')->dayName,
        ]);
    }

    /**
     * Calcular días laborables entre dos fechas
     */
    public function calcularDiasLaborables(Request $request)
    {
        $validated = $request->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
        ]);

        $diasLaborables = DiaFeriado::calcularDiasLaborables(
            $validated['fecha_inicio'],
            $validated['fecha_fin']
        );

        $inicio = Carbon::parse($validated['fecha_inicio']);
        $fin = Carbon::parse($validated['fecha_fin']);
        $diasTotales = $inicio->diffInDays($fin) + 1;

        return response()->json([
            'fecha_inicio' => $validated['fecha_inicio'],
            'fecha_fin' => $validated['fecha_fin'],
            'dias_totales' => $diasTotales,
            'dias_laborables' => $diasLaborables,
            'dias_no_laborables' => $diasTotales - $diasLaborables,
        ]);
    }

    /**
     * Importar feriados predefinidos de Bolivia
     */
    public function importarFeriadosBolivia(Request $request)
    {
        $anio = $request->input('anio', now()->year);

        $feriadosBolivia = [
            ['mes' => 1, 'dia' => 1, 'nombre' => 'Año Nuevo', 'recurrente' => true],
            ['mes' => 1, 'dia' => 22, 'nombre' => 'Día del Estado Plurinacional', 'recurrente' => true],
            ['mes' => 5, 'dia' => 1, 'nombre' => 'Día del Trabajo', 'recurrente' => true],
            ['mes' => 8, 'dia' => 6, 'nombre' => 'Día de la Independencia', 'recurrente' => true],
            ['mes' => 11, 'dia' => 2, 'nombre' => 'Día de Todos los Santos', 'recurrente' => true],
            ['mes' => 12, 'dia' => 25, 'nombre' => 'Navidad', 'recurrente' => true],
        ];

        $importados = 0;
        foreach ($feriadosBolivia as $feriado) {
            $fecha = Carbon::create($anio, $feriado['mes'], $feriado['dia']);
            
            // Verificar si ya existe
            $existe = DiaFeriado::where('fecha', $fecha->format('Y-m-d'))->exists();
            
            if (!$existe) {
                DiaFeriado::create([
                    'fecha' => $fecha,
                    'nombre' => $feriado['nombre'],
                    'recurrente' => $feriado['recurrente'],
                    'activo' => true,
                ]);
                $importados++;
            }
        }

        return response()->json([
            'message' => "Se importaron {$importados} feriados para el año {$anio}",
            'importados' => $importados,
            'anio' => $anio
        ]);
    }

    /**
     * Marcar todos los domingos de un año como no laborables
     */
    public function marcarDomingos(Request $request)
    {
        $anio = $request->input('anio', now()->year);
        
        $inicio = Carbon::create($anio, 1, 1);
        $fin = Carbon::create($anio, 12, 31);
        
        $domingos = 0;
        $current = $inicio->copy();
        
        // Encontrar el primer domingo
        while (!$current->isSunday()) {
            $current->addDay();
        }
        
        // Marcar todos los domingos del año
        while ($current->lte($fin)) {
            $fecha = $current->format('Y-m-d');
            
            // Verificar si ya existe
            $existe = DiaFeriado::where('fecha', $fecha)->exists();
            
            if (!$existe) {
                DiaFeriado::create([
                    'fecha' => $fecha,
                    'nombre' => 'Domingo',
                    'descripcion' => 'Día de descanso semanal',
                    'recurrente' => false,
                    'activo' => true,
                ]);
                $domingos++;
            }
            
            $current->addWeek();
        }
        
        return response()->json([
            'message' => "Se marcaron {$domingos} domingos como no laborables para el año {$anio}",
            'domingos_marcados' => $domingos,
            'anio' => $anio
        ]);
    }

    /**
     * Marcar todos los fines de semana (sábados y domingos) de un año como no laborables
     */
    public function marcarFinesDeSemana(Request $request)
    {
        $anio = $request->input('anio', now()->year);
        
        $inicio = Carbon::create($anio, 1, 1);
        $fin = Carbon::create($anio, 12, 31);
        
        $diasMarcados = 0;
        $current = $inicio->copy();
        
        while ($current->lte($fin)) {
            // Si es sábado o domingo
            if ($current->isWeekend()) {
                $fecha = $current->format('Y-m-d');
                
                // Verificar si ya existe
                $existe = DiaFeriado::where('fecha', $fecha)->exists();
                
                if (!$existe) {
                    $nombreDia = $current->isSaturday() ? 'Sábado' : 'Domingo';
                    
                    DiaFeriado::create([
                        'fecha' => $fecha,
                        'nombre' => $nombreDia,
                        'descripcion' => 'Fin de semana',
                        'recurrente' => false,
                        'activo' => true,
                    ]);
                    $diasMarcados++;
                }
            }
            
            $current->addDay();
        }
        
        return response()->json([
            'message' => "Se marcaron {$diasMarcados} días de fin de semana como no laborables para el año {$anio}",
            'dias_marcados' => $diasMarcados,
            'anio' => $anio
        ]);
    }
}
