<?php

namespace App\Http\Controllers;

use App\Models\Comentario;
use App\Models\Tarea;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ComentarioController extends Controller
{
    public function index(Tarea $tarea)
    {
        $comentarios = $tarea->comentarios()
            ->with('usuario:id,name,email')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($comentarios);
    }

    public function store(Request $request, Tarea $tarea)
    {
        $validated = $request->validate([
            'contenido' => 'required|string',
        ]);

        $comentario = Comentario::create([
            'tarea_id' => $tarea->id,
            'usuario_id' => Auth::id(),
            'contenido' => $validated['contenido'],
        ]);

        $comentario->load('usuario:id,name,email');

        return response()->json([
            'message' => 'Comentario agregado exitosamente',
            'comentario' => $comentario
        ], 201);
    }

    public function destroy(Comentario $comentario)
    {
        // Verificar que el usuario sea el dueño del comentario
        if ($comentario->usuario_id !== Auth::id()) {
            return response()->json([
                'message' => 'No autorizado para eliminar este comentario'
            ], 403);
        }

        $comentario->delete();

        return response()->json([
            'message' => 'Comentario eliminado exitosamente'
        ]);
    }
}
