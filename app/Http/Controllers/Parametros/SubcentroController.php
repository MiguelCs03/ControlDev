<?php

namespace App\Http\Controllers\Parametros;

use App\Http\Controllers\Controller;
use App\Models\Subcentro;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SubcentroController extends Controller
{
    /**
     * Listar todos los subcentros ACTIVOS
     */
    public function listarSubcentros(Request $request)
    {
        try {
            $subcentro = new Subcentro;
            $result = $subcentro->listarSubcentros($request->search);
            
            return response()->json($result, 200);
        } catch (Exception $err) {
            return response()->json($err->getMessage(), $err->getCode() ?: 500);
        }
    }

    /**
     * Crear un nuevo subcentro
     */
    public function crearSubcentro(Request $request)
    {
        try {
            DB::beginTransaction();
            
            $request->validate([
                'DESCRIPCION' => 'required|string|max:50',
            ]);
            
            $subcentro = new Subcentro;
            
            $datos = [
                'DESCRIPCION' => $request->DESCRIPCION,
            ];
            
            // Agregar usuario que crea
            if (Auth::check()) {
                $datos['CREADO_POR'] = Auth::id();
            }
            
            $result = $subcentro->crearSubcentro($datos);
            
            DB::commit();
            return response()->json($result, 201);
        } catch (Exception $err) {
            DB::rollback();
            return response()->json($err->getMessage(), $err->getCode() ?: 500);
        }
    }

    /**
     * Editar subcentro existente (solo activos)
     */
    public function editarSubcentro(Request $request, $id)
    {
        try {
            DB::beginTransaction();
            
            $request->validate([
                'DESCRIPCION' => 'required|string|max:50',
            ]);
            
            $subcentro = new Subcentro;
            
            $datos = [
                'DESCRIPCION' => $request->DESCRIPCION,
            ];
            
            // Agregar usuario que modifica
            if (Auth::check()) {
                $datos['MODIFICADO_POR'] = Auth::id();
            }
            
            $result = $subcentro->editarSubcentro($id, $datos);
            
            DB::commit();
            return response()->json($result, 200);
        } catch (Exception $err) {
            DB::rollback();
            return response()->json($err->getMessage(), $err->getCode() ?: 500);
        }
    }

    /**
     * ELIMINAR subcentro (poner ACTIVO = 0)
     */
    public function eliminarSubcentro($id)
    {
        try {
            $subcentro = new Subcentro;
            $result = $subcentro->eliminarSubcentro($id);
            
            return response()->json([
                'message' => "Subcentro eliminado exitosamente",
                'data' => $result
            ], 200);
        } catch (Exception $err) {
            return response()->json($err->getMessage(), $err->getCode() ?: 500);
        }
    }
}