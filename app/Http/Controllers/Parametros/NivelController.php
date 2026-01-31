<?php

namespace App\Http\Controllers\Parametros;

use App\Http\Controllers\Controller;
use App\Models\Nivel;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class NivelController extends Controller
{
    /**
     * Listar todos los niveles ACTIVOS
     */
    public function listarNiveles(Request $request)
    {
        try {
            $nivel = new Nivel;
            $result = $nivel->listarNiveles($request->search);
            
            return response()->json($result, 200);
        } catch (Exception $err) {
            return response()->json($err->getMessage(), $err->getCode() ?: 500);
        }
    }

    /**
     * Crear un nuevo nivel
     */
    public function crearNivel(Request $request)
    {
        try {
            DB::beginTransaction();
            
            $request->validate([
                'nombre' => 'required|string|max:100',
                'categoria' => 'required|string|max:100',
                'midpoint' => 'required|integer|min:0',
            ]);
            
            $nivel = new Nivel;
            
            $datos = [
                'nombre' => $request->nombre,
                'categoria' => $request->categoria,
                'midpoint' => $request->midpoint,
            ];
            
            // Agregar usuario que crea
            if (Auth::check()) {
                $datos['creado_por'] = Auth::id();
            }
            
            $result = $nivel->crearNivel($datos);
            
            DB::commit();
            return response()->json($result, 201);
        } catch (Exception $err) {
            DB::rollback();
            return response()->json($err->getMessage(), $err->getCode() ?: 500);
        }
    }

    /**
     * Editar nivel existente (solo activos)
     */
    public function editarNivel(Request $request, $id)
    {
        try {
            DB::beginTransaction();
            
            $request->validate([
                'nombre' => 'required|string|max:100',
                'categoria' => 'required|string|max:100',
                'midpoint' => 'required|integer|min:0',
            ]);
            
            $nivel = new Nivel;
            
            $datos = [
                'nombre' => $request->nombre,
                'categoria' => $request->categoria,
                'midpoint' => $request->midpoint,
            ];
            
            // Agregar usuario que modifica
            if (Auth::check()) {
                $datos['modificado_por'] = Auth::id();
            }
            
            $result = $nivel->editarNivel($id, $datos);
            
            DB::commit();
            return response()->json($result, 200);
        } catch (Exception $err) {
            DB::rollback();
            return response()->json($err->getMessage(), $err->getCode() ?: 500);
        }
    }

    /**
     * ELIMINAR nivel (poner ACTIVO = 0)
     */
    public function eliminarNivel($id)
    {
        try {
            $nivel = new Nivel;
            $result = $nivel->eliminarNivel($id);
            
            return response()->json([
                'message' => "Nivel eliminado exitosamente",
                'data' => $result
            ], 200);
        } catch (Exception $err) {
            return response()->json($err->getMessage(), $err->getCode() ?: 500);
        }
    }
}