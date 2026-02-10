<?php

namespace App\Http\Controllers;

use App\Models\Adjunto;
use App\Models\Tarea;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AdjuntoController extends Controller
{
    public function index(Tarea $tarea)
    {
        $usuario = Auth::user();
        
        // Verificar si el usuario tiene permiso para ver los adjuntos
        // Solo puede ver si:
        // 1. Es administrador (tiene el rol admin)
        // 2. Es el responsable de la tarea (desarrollador asignado)
        // 3. Es el creador de la tarea
        $esAdmin = $usuario->hasRole(['admin', 'administrador', 'administrator']);
        $esResponsable = $tarea->responsable_id === $usuario->id;
        $esCreador = $tarea->creador_id === $usuario->id;
        
        if (!$esAdmin && !$esResponsable && !$esCreador) {
            return response()->json([
                'message' => 'No tienes permiso para ver los adjuntos de esta tarea'
            ], 403);
        }
        
        $adjuntos = $tarea->adjuntos()
            ->with('usuario:id,name')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($adjuntos);
    }

    public function store(Request $request, Tarea $tarea)
    {
        \Log::info('=== Iniciando subida de archivo ===', [
            'tarea_id' => $tarea->id,
            'usuario_id' => Auth::id(),
            'tiene_archivo' => $request->hasFile('archivo')
        ]);
        
        $usuario = Auth::user();
        
        // Solo los administradores pueden subir archivos
        $esAdmin = $usuario->hasRole(['admin', 'administrador', 'administrator']);
        
        \Log::info('Verificación de permisos', [
            'usuario_id' => $usuario->id,
            'es_admin' => $esAdmin,
            'roles' => $usuario->roles->pluck('name')
        ]);
        
        if (!$esAdmin) {
            \Log::warning('Permiso denegado para subir archivo', [
                'usuario_id' => $usuario->id
            ]);
            return response()->json([
                'message' => 'Solo los administradores pueden subir archivos a las tareas'
            ], 403);
        }
        
        try {
            $request->validate([
                'archivo' => 'required|file|mimes:doc,docx,xls,xlsx,ppt,pptx,txt,pdf,jpg,jpeg,png,gif|max:10240', // Max 10MB
            ]);
            
            \Log::info('Validación exitosa');

            $archivo = $request->file('archivo');
            $nombreOriginal = $archivo->getClientOriginalName();
            $ruta = $archivo->store('adjuntos_tareas', 'public');
            
            \Log::info('Archivo almacenado', [
                'nombre_original' => $nombreOriginal,
                'ruta' => $ruta,
                'tipo' => $archivo->getClientMimeType(),
                'tamano' => $archivo->getSize()
            ]);

            $adjunto = Adjunto::create([
                'tarea_id' => $tarea->id,
                'nombre_archivo' => $nombreOriginal,
                'ruta_archivo' => $ruta,
                'tipo_archivo' => $archivo->getClientMimeType(),
                'tamano' => $archivo->getSize(),
                'usuario_id' => Auth::id(),
            ]);

            $adjunto->load('usuario:id,name');
            
            \Log::info('Adjunto creado exitosamente', [
                'adjunto_id' => $adjunto->id
            ]);

            return response()->json([
                'message' => 'Archivo adjuntado exitosamente',
                'adjunto' => $adjunto
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Error de validación', [
                'errors' => $e->errors()
            ]);
            throw $e;
        } catch (\Exception $e) {
            \Log::error('Error al subir archivo', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'message' => 'Error al subir el archivo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function download(Adjunto $adjunto)
    {
        $usuario = Auth::user();
        $tarea = $adjunto->tarea;
        
        // Verificar permisos para descargar
        $esAdmin = $usuario->hasRole(['admin', 'administrador', 'administrator']);
        $esResponsable = $tarea->responsable_id === $usuario->id;
        $esCreador = $tarea->creador_id === $usuario->id;
        
        if (!$esAdmin && !$esResponsable && !$esCreador) {
            return response()->json([
                'message' => 'No tienes permiso para descargar este archivo'
            ], 403);
        }
        
        // Verificar que el archivo existe
        if (!Storage::disk('public')->exists($adjunto->ruta_archivo)) {
            return response()->json([
                'message' => 'Archivo no encontrado'
            ], 404);
        }

        // Limpiar cualquier buffer de salida existente para evitar corrupción
        if (ob_get_level()) {
            ob_end_clean();
        }

        // Método más directo y robusto usando el Storage de Laravel
        return Storage::disk('public')->download(
            $adjunto->ruta_archivo, 
            $adjunto->nombre_archivo, 
            ['Content-Type' => $adjunto->tipo_archivo]
        );
    }

    public function destroy(Adjunto $adjunto)
    {
        $usuario = Auth::user();
        
        // Solo los administradores pueden eliminar archivos
        $esAdmin = $usuario->hasRole(['admin', 'administrador', 'administrator']);
        
        if (!$esAdmin) {
            return response()->json([
                'message' => 'Solo los administradores pueden eliminar archivos'
            ], 403);
        }
        
        // Eliminar archivo del storage
        if (Storage::disk('public')->exists($adjunto->ruta_archivo)) {
            Storage::disk('public')->delete($adjunto->ruta_archivo);
        }

        $adjunto->delete();

        return response()->json([
            'message' => 'Archivo eliminado exitosamente'
        ]);
    }
}
