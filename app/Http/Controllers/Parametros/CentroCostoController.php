<?php

namespace App\Http\Controllers\Parametros;

use App\Http\Controllers\Controller;
use App\Models\CentroCosto;
use App\Models\Unidad;
use App\Models\Subcentro;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CentroCostoController extends Controller
{
    /**
     * Listar todos los centros de costo ACTIVOS
     */
    public function listarCentrosCosto(Request $request)
    {
        try {
            $centroCosto = new CentroCosto;
            $result = $centroCosto->listarCentrosCosto($request->search);
            
            return response()->json($result, 200);
        } catch (Exception $err) {
            return response()->json($err->getMessage(), $err->getCode() ?: 500);
        }
    }

    /**
     * Obtener unidades y subcentros activos para selects
     */
    public function obtenerDependencias()
    {
        try {
            $unidades = Unidad::where('ACTIVO', 1)
                ->orderBy('DESCRIPCION')
                ->get(['COD_UNIDAD', 'DESCRIPCION']);
            
            $subcentros = Subcentro::where('ACTIVO', 1)
                ->orderBy('DESCRIPCION')
                ->get(['COD_SUBCENTRO', 'DESCRIPCION']);
            
            return response()->json([
                'unidades' => $unidades,
                'subcentros' => $subcentros
            ], 200);
        } catch (Exception $err) {
            return response()->json($err->getMessage(), $err->getCode() ?: 500);
        }
    }

    /**
     * Crear un nuevo centro de costo
     */
    public function crearCentroCosto(Request $request)
    {
        try {
            DB::beginTransaction();
            
            $request->validate([
                'DESCRIPCION' => 'required|string|max:50',
                'COD_SUBCENTRO' => 'required|integer|exists:subcentro,COD_SUBCENTRO',
                'COD_UNIDAD' => 'required|integer|exists:unidad,COD_UNIDAD',
                'codigo_base' => 'nullable|integer',
                // COD_CENTRO_BD NO se recibe del frontend
            ]);
            
            $centroCosto = new CentroCosto;
            
            $datos = [
                'DESCRIPCION' => $request->DESCRIPCION,
                'COD_SUBCENTRO' => $request->COD_SUBCENTRO,
                'COD_UNIDAD' => $request->COD_UNIDAD,
                'codigo_base' => $request->codigo_base,
                // COD_CENTRO_BD se asigna automáticamente en el modelo
            ];
            
            // Agregar usuario que crea
            if (Auth::check()) {
                $datos['CREADO_POR'] = Auth::id();
            }
            
            $result = $centroCosto->crearCentroCosto($datos);
            
            DB::commit();
            return response()->json($result, 201);
        } catch (Exception $err) {
            DB::rollback();
            return response()->json($err->getMessage(), $err->getCode() ?: 500);
        }
    }

    /**
     * Editar centro de costo existente (solo activos)
     */
    public function editarCentroCosto(Request $request, $id)
    {
        try {
            DB::beginTransaction();
            
            $request->validate([
                'DESCRIPCION' => 'required|string|max:50',
                'COD_SUBCENTRO' => 'required|integer|exists:subcentro,COD_SUBCENTRO',
                'COD_UNIDAD' => 'required|integer|exists:unidad,COD_UNIDAD',
                'codigo_base' => 'nullable|integer',
                // COD_CENTRO_BD NO se puede editar desde el frontend
            ]);
            
            $centroCosto = new CentroCosto;
            
            $datos = [
                'DESCRIPCION' => $request->DESCRIPCION,
                'COD_SUBCENTRO' => $request->COD_SUBCENTRO,
                'COD_UNIDAD' => $request->COD_UNIDAD,
                'codigo_base' => $request->codigo_base,
                // COD_CENTRO_BD NO se incluye
            ];
            
            // Agregar usuario que modifica
            if (Auth::check()) {
                $datos['MODIFICADO_POR'] = Auth::id();
            }
            
            $result = $centroCosto->editarCentroCosto($id, $datos);
            
            DB::commit();
            return response()->json($result, 200);
        } catch (Exception $err) {
            DB::rollback();
            return response()->json($err->getMessage(), $err->getCode() ?: 500);
        }
    }

    /**
     * ELIMINAR centro de costo (poner ACTIVO = 0)
     */
    public function eliminarCentroCosto($id)
    {
        try {
            $centroCosto = new CentroCosto;
            $result = $centroCosto->eliminarCentroCosto($id);
            
            return response()->json([
                'message' => "Centro de costo eliminado exitosamente",
                'data' => $result
            ], 200);
        } catch (Exception $err) {
            return response()->json($err->getMessage(), $err->getCode() ?: 500);
        }
    }
}