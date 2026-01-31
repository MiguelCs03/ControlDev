<?php

namespace App\Http\Controllers\Parametros;

use App\Http\Controllers\Controller;
use App\Models\Cargo;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class CargoController extends Controller
{
    /**
     * Listar todos los cargos ACTIVOS
     */
    public function listarCargos(Request $request)
    {
        try {
            $cargo = new Cargo;
            $result = $cargo->listarCargos($request->search);
            
            return response()->json($result, 200);
        } catch (Exception $err) {
            return response()->json($err->getMessage(), $err->getCode() ?: 500);
        }
    }

    /**
     * Crear un nuevo cargo
     */
    public function crearCargo(Request $request)
    {
        try {
            DB::beginTransaction();
            
            $request->validate([
                'DESCRIPCION' => 'required|string|max:150',
            ]);
            
            $cargo = new Cargo;
            
            $datos = [
                'DESCRIPCION' => $request->DESCRIPCION,
            ];
            
            // Agregar usuario que crea
            if (Auth::check()) {
                $datos['CREADO_POR'] = Auth::id();
            }
            
            $result = $cargo->crearCargo($datos);
            
            DB::commit();
            return response()->json($result, 201);
        } catch (Exception $err) {
            DB::rollback();
            return response()->json($err->getMessage(), $err->getCode() ?: 500);
        }
    }

    /**
     * Editar cargo existente (solo activos)
     */
    public function editarCargo(Request $request, $id)
    {
        try {
            DB::beginTransaction();
            
            $request->validate([
                'DESCRIPCION' => 'required|string|max:150',
            ]);
            
            $cargo = new Cargo;
            
            $datos = [
                'DESCRIPCION' => $request->DESCRIPCION,
            ];
            
            // Agregar usuario que modifica
            if (Auth::check()) {
                $datos['MODIFICADO_POR'] = Auth::id();
            }
            
            $result = $cargo->editarCargo($id, $datos);
            
            DB::commit();
            return response()->json($result, 200);
        } catch (Exception $err) {
            DB::rollback();
            return response()->json($err->getMessage(), $err->getCode() ?: 500);
        }
    }

    /**
     * ELIMINAR cargo (poner ACTIVO = 0)
     */
    public function eliminarCargo($id)
    {
        try {
            $cargo = new Cargo;
            $result = $cargo->eliminarCargo($id);
            
            return response()->json([
                'message' => "Cargo eliminado exitosamente",
                'data' => $result
            ], 200);
        } catch (Exception $err) {
            return response()->json($err->getMessage(), $err->getCode() ?: 500);
        }
    }
}