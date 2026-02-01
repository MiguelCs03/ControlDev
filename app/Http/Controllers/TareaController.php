<?php

namespace App\Http\Controllers;

use App\Models\Tarea;
use App\Models\RegistroTiempo;
use App\Exports\TareasExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class TareaController extends Controller
{
    public function index(Request $request)
    {
        $query = Tarea::with([
            'proyecto.cliente',
            'responsable:id,name,email',
            'creador:id,name,email',
            'comentarios.usuario:id,name',
            'adjuntos'
        ]);

        // Filtros
        if ($request->has('proyecto_id')) {
            $query->where('proyecto_id', $request->proyecto_id);
        }

        if ($request->has('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->has('responsable_id')) {
            $query->where('responsable_id', $request->responsable_id);
        }

        if ($request->has('prioridad')) {
            $query->where('prioridad', $request->prioridad);
        }

        $tareas = $query->orderBy('created_at', 'desc')->get();

        return response()->json($tareas);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'titulo' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'estado' => 'in:pendiente,en_proceso,en_revision,finalizado,cancelado',
            'prioridad' => 'in:baja,media,alta,urgente',
            'proyecto_id' => 'required|exists:proyectos,id',
            'responsable_id' => 'nullable|exists:users,id',
        ]);

        $validated['creador_id'] = Auth::id();

        $tarea = Tarea::create($validated);
        $tarea->load(['proyecto.cliente', 'responsable', 'creador', 'bitacora.usuario']);

        return response()->json([
            'message' => 'Tarea creada exitosamente',
            'tarea' => $tarea
        ], 201);
    }

    public function show(Tarea $tarea)
    {
        $tarea->load([
            'proyecto.cliente',
            'responsable',
            'creador',
            'comentarios.usuario',
            'adjuntos.usuario',
            'registrosTiempos.usuario',
            'bitacora.usuario'
        ]);

        return response()->json($tarea);
    }

    public function update(Request $request, Tarea $tarea)
    {
        $validated = $request->validate([
            'titulo' => 'string|max:255',
            'descripcion' => 'nullable|string',
            'estado' => 'in:pendiente,en_proceso,en_revision,finalizado,cancelado',
            'prioridad' => 'in:baja,media,alta,urgente',
            'responsable_id' => 'nullable|exists:users,id',
            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date',
        ]);

        $tarea->update($validated);
        $tarea->load(['proyecto.cliente', 'responsable', 'creador', 'bitacora.usuario']);

        return response()->json([
            'message' => 'Tarea actualizada exitosamente',
            'tarea' => $tarea
        ]);
    }

    public function destroy(Tarea $tarea)
    {
        $tarea->delete();

        return response()->json([
            'message' => 'Tarea eliminada exitosamente'
        ]);
    }

    // Actualizar solo el estado de la tarea
    public function actualizarEstado(Request $request, Tarea $tarea)
    {
        $validated = $request->validate([
            'estado' => 'required|in:pendiente,en_proceso,en_revision,finalizado,cancelado',
        ]);

        $tarea->update(['estado' => $validated['estado']]);
        $tarea->load(['proyecto.cliente', 'responsable', 'creador']);

        return response()->json([
            'message' => 'Estado actualizado exitosamente',
            'tarea' => $tarea
        ]);
    }

    // Iniciar registro de tiempo
    public function iniciarTiempo(Tarea $tarea)
    {
        $usuario = Auth::user();

        // Verificar si ya hay un tiempo activo
        $tiempoActivo = RegistroTiempo::where('tarea_id', $tarea->id)
            ->where('usuario_id', $usuario->id)
            ->whereNull('fecha_fin')
            ->first();

        if ($tiempoActivo) {
            return response()->json([
                'message' => 'Ya tienes un registro de tiempo activo en esta tarea',
                'registro' => $tiempoActivo
            ], 422);
        }

        $registro = RegistroTiempo::create([
            'tarea_id' => $tarea->id,
            'usuario_id' => $usuario->id,
            'fecha_inicio' => now(),
        ]);

        // Si la tarea estaba pendiente, pasarla a "en_proceso"
        if ($tarea->estado === 'pendiente') {
            $tarea->update(['estado' => 'en_proceso']);
        }

        return response()->json([
            'message' => 'Registro de tiempo iniciado',
            'registro' => $registro
        ]);
    }

    // Detener registro de tiempo
    public function detenerTiempo(Tarea $tarea, Request $request)
    {
        $usuario = Auth::user();

        $tiempoActivo = RegistroTiempo::where('tarea_id', $tarea->id)
            ->where('usuario_id', $usuario->id)
            ->whereNull('fecha_fin')
            ->first();

        if (!$tiempoActivo) {
            return response()->json([
                'message' => 'No hay un registro de tiempo activo en esta tarea'
            ], 422);
        }

        $validated = $request->validate([
            'nota' => 'nullable|string',
        ]);

        $tiempoActivo->update([
            'fecha_fin' => now(),
            'nota' => $validated['nota'] ?? null,
        ]);

        // Recargar para obtener el tiempo calculado
        $tiempoActivo->refresh();

        return response()->json([
            'message' => 'Registro de tiempo detenido',
            'registro' => $tiempoActivo,
            'tiempo_trabajado' => $tiempoActivo->tiempo_transcurrido . ' minutos'
        ]);
    }

    // Obtener tareas agrupadas por estado (para vista Kanban)
    public function kanban(Request $request)
    {
        $query = Tarea::with([
            'proyecto.cliente',
            'responsable:id,name,email',
            'creador:id,name,email'
        ]);

        // Filtrar por proyecto si se proporciona
        if ($request->has('proyecto_id')) {
            $query->where('proyecto_id', $request->proyecto_id);
        }

        // Filtrar por responsable
        if ($request->has('responsable_id')) {
            $query->where('responsable_id', $request->responsable_id);
        }

        $tareas = $query->get();

        // Agrupar por estado
        $kanban = [
            'pendiente' => $tareas->where('estado', 'pendiente')->values(),
            'en_proceso' => $tareas->where('estado', 'en_proceso')->values(),
            'en_revision' => $tareas->where('estado', 'en_revision')->values(),
            'finalizado' => $tareas->where('estado', 'finalizado')->values(),
        ];

        return response()->json($kanban);
    }

    // Dashboard con estadísticas generales
    public function dashboard()
    {
        $usuario = Auth::user();

        $estadisticas = [
            'mis_tareas' => [
                'total' => Tarea::where('responsable_id', $usuario->id)->count(),
                'pendientes' => Tarea::where('responsable_id', $usuario->id)->where('estado', 'pendiente')->count(),
                'en_proceso' => Tarea::where('responsable_id', $usuario->id)->where('estado', 'en_proceso')->count(),
                'en_revision' => Tarea::where('responsable_id', $usuario->id)->where('estado', 'en_revision')->count(),
                'finalizadas' => Tarea::where('responsable_id', $usuario->id)->where('estado', 'finalizado')->count(),
            ],
            'todas_las_tareas' => [
                'total' => Tarea::count(),
                'pendientes' => Tarea::where('estado', 'pendiente')->count(),
                'en_proceso' => Tarea::where('estado', 'en_proceso')->count(),
                'en_revision' => Tarea::where('estado', 'en_revision')->count(),
                'finalizadas' => Tarea::where('estado', 'finalizado')->count(),
            ],
            'por_prioridad' => [
                'urgente' => Tarea::where('responsable_id', $usuario->id)->where('prioridad', 'urgente')->whereIn('estado', ['pendiente', 'en_proceso', 'en_revision'])->count(),
                'alta' => Tarea::where('responsable_id', $usuario->id)->where('prioridad', 'alta')->whereIn('estado', ['pendiente', 'en_proceso', 'en_revision'])->count(),
                'media' => Tarea::where('responsable_id', $usuario->id)->where('prioridad', 'media')->whereIn('estado', ['pendiente', 'en_proceso', 'en_revision'])->count(),
                'baja' => Tarea::where('responsable_id', $usuario->id)->where('prioridad', 'baja')->whereIn('estado', ['pendiente', 'en_proceso', 'en_revision'])->count(),
            ],
            'tareas_recientes' => Tarea::where('responsable_id', $usuario->id)
                ->with(['proyecto.cliente'])
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get(),
        ];

        return response()->json($estadisticas);
    }

    // Obtener bitácora de una tarea
    public function bitacora(Tarea $tarea)
    {
        $bitacora = $tarea->bitacora()->with('usuario')->get();

        return response()->json($bitacora);
    }

    // Exportar las tareas del usuario autenticado
    public function exportarMisTareas(Request $request)
    {
        $usuarioId = Auth::id();
        $proyectoId = $request->query('proyecto_id');

        $nombreArchivo = 'mis_tareas_' . date('Y-m-d_His') . '.xlsx';

        return Excel::download(
            new TareasExport($usuarioId, $proyectoId, false),
            $nombreArchivo
        );
    }

    // Exportar tareas (solo admin)
    public function exportarTareasAdmin(Request $request)
    {
        // Verificar si el usuario es admin (ajustar según tu lógica de roles)
        // Por ahora, cualquier usuario autenticado puede exportar
        
        $usuarioId = $request->query('usuario_id');
        $proyectoId = $request->query('proyecto_id');

        $sufijo = '';
        if ($usuarioId) {
            $usuario = \App\Models\User::find($usuarioId);
            $sufijo = '_' . ($usuario ? str_slug($usuario->name) : 'usuario_' . $usuarioId);
        }
        if ($proyectoId) {
            $proyecto = \App\Models\Proyecto::find($proyectoId);
            $sufijo .= '_' . ($proyecto ? str_slug($proyecto->nombre) : 'proyecto_' . $proyectoId);
        }

        $nombreArchivo = 'reporte_tareas' . $sufijo . '_' . date('Y-m-d_His') . '.xlsx';

        return Excel::download(
            new TareasExport($usuarioId, $proyectoId, true),
            $nombreArchivo
        );
    }
}
