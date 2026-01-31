<?php

namespace App\Http\Controllers\Parametros;

use App\Http\Controllers\Controller;
use App\Models\TipoJustificacion;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TipoJustificacionController extends Controller
{
    /**
     * Listar todos los tipos de justificación ACTIVOS
     */
    public function listarTiposJustificacion(Request $request)
    {
        try {
            $tipoJustificacion = new TipoJustificacion;
            $result = $tipoJustificacion->listarTiposJustificacion($request->search);
            
            return response()->json($result, 200);
        } catch (Exception $err) {
            return response()->json($err->getMessage(), $err->getCode() ?: 500);
        }
    }

    /**
     * Crear un nuevo tipo de justificación
     */
    public function crearTipoJustificacion(Request $request)
    {
        try {
            DB::beginTransaction();
            
            $request->validate([
                'nombre' => 'required|string|max:100',
            ]);
            
            $tipoJustificacion = new TipoJustificacion;
            
            $datos = [
                'nombre' => $request->nombre,
            ];
            
            // Agregar usuario que crea
            if (Auth::check()) {
                $datos['creado_por'] = Auth::id();
            }
            
            $result = $tipoJustificacion->crearTipoJustificacion($datos);
            
            DB::commit();
            return response()->json($result, 201);
        } catch (Exception $err) {
            DB::rollback();
            return response()->json($err->getMessage(), $err->getCode() ?: 500);
        }
    }

    /**
     * Editar tipo de justificación existente (solo activos)
     */
    public function editarTipoJustificacion(Request $request, $id)
    {
        try {
            DB::beginTransaction();
            
            $request->validate([
                'nombre' => 'required|string|max:100',
            ]);
            
            $tipoJustificacion = new TipoJustificacion;
            
            $datos = [
                'nombre' => $request->nombre,
            ];
            
            // Agregar usuario que modifica
            if (Auth::check()) {
                $datos['modificado_por'] = Auth::id();
            }
            
            $result = $tipoJustificacion->editarTipoJustificacion($id, $datos);
            
            DB::commit();
            return response()->json($result, 200);
        } catch (Exception $err) {
            DB::rollback();
            return response()->json($err->getMessage(), $err->getCode() ?: 500);
        }
    }

    /**
     * ELIMINAR tipo de justificación (poner ACTIVO = 0)
     */
    public function eliminarTipoJustificacion($id)
    {
        try {
            $tipoJustificacion = new TipoJustificacion;
            $result = $tipoJustificacion->eliminarTipoJustificacion($id);
            
            return response()->json([
                'message' => "Tipo de justificación eliminado exitosamente",
                'data' => $result
            ], 200);
        } catch (Exception $err) {
            return response()->json($err->getMessage(), $err->getCode() ?: 500);
        }
    }
}