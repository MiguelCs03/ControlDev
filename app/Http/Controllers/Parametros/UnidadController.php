<?php

namespace App\Http\Controllers\Parametros;

use App\Http\Controllers\Controller;
use App\Models\Unidad;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UnidadController extends Controller
{
    /**
     * Listar todas las unidades ACTIVAS
     */
    public function listarUnidades(Request $request)
    {
        try {
            $unidad = new Unidad;
            $result = $unidad->listarUnidades($request->search);
            
            return response()->json($result, 200);
        } catch (Exception $err) {
            return response()->json($err->getMessage(), $err->getCode() ?: 500);
        }
    }

    /**
     * Crear una nueva unidad
     */
    public function crearUnidad(Request $request)
    {
        try {
            DB::beginTransaction();
            
            $request->validate([
                'DESCRIPCION' => 'required|string|max:50',
            ]);
            
            $unidad = new Unidad;
            
            $datos = [
                'DESCRIPCION' => $request->DESCRIPCION,
            ];
            
            // Agregar usuario que crea
            if (Auth::check()) {
                $datos['CREADO_POR'] = Auth::id();
            }
            
            $result = $unidad->crearUnidad($datos);
            
            DB::commit();
            return response()->json($result, 201);
        } catch (Exception $err) {
            DB::rollback();
            return response()->json($err->getMessage(), $err->getCode() ?: 500);
        }
    }

    /**
     * Editar unidad existente (solo activas)
     */
    public function editarUnidad(Request $request, $id)
    {
        try {
            DB::beginTransaction();
            
            $request->validate([
                'DESCRIPCION' => 'required|string|max:50',
            ]);
            
            $unidad = new Unidad;
            
            $datos = [
                'DESCRIPCION' => $request->DESCRIPCION,
            ];
            
            // Agregar usuario que modifica
            if (Auth::check()) {
                $datos['MODIFICADO_POR'] = Auth::id();
            }
            
            $result = $unidad->editarUnidad($id, $datos);
            
            DB::commit();
            return response()->json($result, 200);
        } catch (Exception $err) {
            DB::rollback();
            return response()->json($err->getMessage(), $err->getCode() ?: 500);
        }
    }

    /**
     * ELIMINAR unidad (poner ACTIVO = 0)
     */
    public function eliminarUnidad($id)
    {
        try {
            $unidad = new Unidad;
            $result = $unidad->eliminarUnidad($id);
            
            return response()->json([
                'message' => "Unidad eliminada exitosamente",
                'data' => $result
            ], 200);
        } catch (Exception $err) {
            return response()->json($err->getMessage(), $err->getCode() ?: 500);
        }
    }
}