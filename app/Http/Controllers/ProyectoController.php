<?php

namespace App\Http\Controllers;

use App\Models\Proyecto;
use Illuminate\Http\Request;

class ProyectoController extends Controller
{
    public function index(Request $request)
    {
        $query = Proyecto::with(['cliente', 'tareas' => function ($q) {
            $q->with(['responsable:id,name', 'creador:id,name']);
        }]);

        // Filtrar por cliente si se proporciona
        if ($request->has('cliente_id')) {
            $query->where('cliente_id', $request->cliente_id);
        }

        // Filtrar por estado
        if ($request->has('estado')) {
            $query->where('estado', $request->estado);
        }

        $proyectos = $query->orderBy('created_at', 'desc')->get();

        // Agregar información calculada
        $proyectos = $proyectos->map(function ($proyecto) {
            return [
                'id' => $proyecto->id,
                'nombre' => $proyecto->nombre,
                'descripcion' => $proyecto->descripcion,
                'estado' => $proyecto->estado,
                'cliente' => $proyecto->cliente,
                'fecha_inicio' => $proyecto->fecha_inicio,
                'fecha_fin_estimada' => $proyecto->fecha_fin_estimada,
                'fecha_fin_real' => $proyecto->fecha_fin_real,
                'progreso' => $proyecto->progreso,
                'tareas_pendientes' => $proyecto->tareas_pendientes,
                'total_tareas' => $proyecto->tareas->count(),
                'tareas' => $proyecto->tareas,
                'created_at' => $proyecto->created_at,
                'updated_at' => $proyecto->updated_at,
            ];
        });

        return response()->json($proyectos);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'estado' => 'in:activo,pausado,finalizado,cancelado',
            'cliente_id' => 'required|exists:clientes,id',
            'fecha_inicio' => 'nullable|date',
            'fecha_fin_estimada' => 'nullable|date|after_or_equal:fecha_inicio',
        ]);

        $proyecto = Proyecto::create($validated);
        $proyecto->load('cliente');

        return response()->json([
            'message' => 'Proyecto creado exitosamente',
            'proyecto' => $proyecto
        ], 201);
    }

    public function show(Proyecto $proyecto)
    {
        $proyecto->load([
            'cliente',
            'tareas.responsable',
            'tareas.creador',
            'tareas.comentarios.usuario',
            'tareas.adjuntos'
        ]);

        return response()->json([
            'id' => $proyecto->id,
            'nombre' => $proyecto->nombre,
            'descripcion' => $proyecto->descripcion,
            'estado' => $proyecto->estado,
            'cliente' => $proyecto->cliente,
            'fecha_inicio' => $proyecto->fecha_inicio,
            'fecha_fin_estimada' => $proyecto->fecha_fin_estimada,
            'fecha_fin_real' => $proyecto->fecha_fin_real,
            'progreso' => $proyecto->progreso,
            'tareas_pendientes' => $proyecto->tareas_pendientes,
            'total_tareas' => $proyecto->tareas->count(),
            'tareas' => $proyecto->tareas,
            'created_at' => $proyecto->created_at,
            'updated_at' => $proyecto->updated_at,
        ]);
    }

    public function update(Request $request, Proyecto $proyecto)
    {
        $validated = $request->validate([
            'nombre' => 'string|max:255',
            'descripcion' => 'nullable|string',
            'estado' => 'in:activo,pausado,finalizado,cancelado',
            'cliente_id' => 'exists:clientes,id',
            'fecha_inicio' => 'nullable|date',
            'fecha_fin_estimada' => 'nullable|date',
            'fecha_fin_real' => 'nullable|date',
        ]);

        $proyecto->update($validated);
        $proyecto->load('cliente');

        return response()->json([
            'message' => 'Proyecto actualizado exitosamente',
            'proyecto' => $proyecto
        ]);
    }

    public function destroy(Proyecto $proyecto)
    {
        $proyecto->delete();

        return response()->json([
            'message' => 'Proyecto eliminado exitosamente'
        ]);
    }

    // Obtener estadísticas del proyecto
    public function estadisticas(Proyecto $proyecto)
    {
        $tareas = $proyecto->tareas;

        $estadisticas = [
            'total_tareas' => $tareas->count(),
            'pendientes' => $tareas->where('estado', 'pendiente')->count(),
            'en_proceso' => $tareas->where('estado', 'en_proceso')->count(),
            'en_revision' => $tareas->where('estado', 'en_revision')->count(),
            'finalizadas' => $tareas->where('estado', 'finalizado')->count(),
            'canceladas' => $tareas->where('estado', 'cancelado')->count(),
            'progreso' => $proyecto->progreso,
            'horas_estimadas' => $tareas->sum('horas_estimadas'),
            'horas_reales' => $tareas->sum('horas_reales'),
        ];

        return response()->json($estadisticas);
    }
}
