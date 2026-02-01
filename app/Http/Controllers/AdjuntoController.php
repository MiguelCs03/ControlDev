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
        $adjuntos = $tarea->adjuntos()
            ->with('usuario:id,name')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($adjuntos);
    }

    public function store(Request $request, Tarea $tarea)
    {
        $request->validate([
            'archivo' => 'required|file|max:10240', // Max 10MB
        ]);

        $archivo = $request->file('archivo');
        $nombreOriginal = $archivo->getClientOriginalName();
        $ruta = $archivo->store('adjuntos_tareas', 'public');

        $adjunto = Adjunto::create([
            'tarea_id' => $tarea->id,
            'nombre_archivo' => $nombreOriginal,
            'ruta_archivo' => $ruta,
            'tipo_archivo' => $archivo->getClientMimeType(),
            'tamano' => $archivo->getSize(),
            'usuario_id' => Auth::id(),
        ]);

        $adjunto->load('usuario:id,name');

        return response()->json([
            'message' => 'Archivo adjuntado exitosamente',
            'adjunto' => $adjunto
        ], 201);
    }

    public function download(Adjunto $adjunto)
    {
        if (!Storage::disk('public')->exists($adjunto->ruta_archivo)) {
            return response()->json([
                'message' => 'Archivo no encontrado'
            ], 404);
        }

        return Storage::disk('public')->download($adjunto->ruta_archivo, $adjunto->nombre_archivo);
    }

    public function destroy(Adjunto $adjunto)
    {
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
