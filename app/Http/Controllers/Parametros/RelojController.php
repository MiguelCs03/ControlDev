<?php

namespace App\Http\Controllers\Parametros;

use App\Http\Controllers\Controller;
use App\Models\Reloj;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RelojController extends Controller
{
    /**
     * Listar relojes activos
     */
    public function listarActivos(Request $request)
    {
        try {
            $reloj = new Reloj;
            $result = $reloj->listarActivos($request->search);
            
            return response()->json($result, 200);
        } catch (Exception $err) {
            return response()->json($err->getMessage(), $err->getCode() ?: 500);
        }
    }

    /**
     * Crear un nuevo reloj
     */
    public function crearReloj(Request $request)
    {
        try {
            DB::beginTransaction();
            
            $request->validate([
                'NOMBRE' => 'required|string|max:30',
                'OC1' => 'required|integer|min:0|max:255',
                'OC2' => 'required|integer|min:0|max:255',
                'OC3' => 'required|integer|min:0|max:255',
                'OC4' => 'required|integer|min:0|max:255',
                'ESTADO' => 'required|integer',
            ]);
            
            $reloj = new Reloj;
            
            $datos = [
                'NOMBRE' => $request->NOMBRE,
                'OC1' => $request->OC1,
                'OC2' => $request->OC2,
                'OC3' => $request->OC3,
                'OC4' => $request->OC4,
                'ESTADO' => $request->ESTADO,
            ];
            
            // Agregar usuario que crea
            if (Auth::check()) {
                $datos['CREADO_POR'] = Auth::id();
            }
            
            // Campos opcionales
            if ($request->has('ULTIMA_DESCARGA')) {
                $datos['ULTIMA_DESCARGA'] = $request->ULTIMA_DESCARGA;
            }
            
            if ($request->has('ULTIMA_DESCARGA_BUENA')) {
                $datos['ULTIMA_DESCARGA_BUENA'] = $request->ULTIMA_DESCARGA_BUENA;
            }
            
            $result = $reloj->crearReloj($datos);
            
            DB::commit();
            return response()->json($result, 201);
        } catch (\Illuminate\Database\QueryException $err) {
            DB::rollback();
            if ($err->getCode() == 23000) {
                return response()->json(['message' => 'La IP ya existe en la base de datos'], 400);
            }
            return response()->json(['message' => $err->getMessage()], 500);
        } catch (Exception $err) {
            DB::rollback();
            return response()->json(['message' => $err->getMessage()], $err->getCode() ?: 500);
        }
    }

    /**
     * Editar reloj existente
     */
    public function editarReloj(Request $request, $id)
    {
        try {
            DB::beginTransaction();
            
            $request->validate([
                'NOMBRE' => 'sometimes|string|max:30',
                'OC1' => 'sometimes|integer|min:0|max:255',
                'OC2' => 'sometimes|integer|min:0|max:255',
                'OC3' => 'sometimes|integer|min:0|max:255',
                'OC4' => 'sometimes|integer|min:0|max:255',
                'ESTADO' => 'sometimes|integer',
            ]);
            
            $reloj = new Reloj;
            
            $datos = [];
            
            if ($request->has('NOMBRE')) {
                $datos['NOMBRE'] = $request->NOMBRE;
            }
            
            if ($request->has('OC1')) {
                $datos['OC1'] = $request->OC1;
            }
            
            if ($request->has('OC2')) {
                $datos['OC2'] = $request->OC2;
            }
            
            if ($request->has('OC3')) {
                $datos['OC3'] = $request->OC3;
            }
            
            if ($request->has('OC4')) {
                $datos['OC4'] = $request->OC4;
            }
            
            if ($request->has('ESTADO')) {
                $datos['ESTADO'] = $request->ESTADO;
            }
            
            // Agregar usuario que modifica
            if (Auth::check()) {
                $datos['MODIFICADO_POR'] = Auth::id();
            }
            
            // Campos opcionales
            if ($request->has('ULTIMA_DESCARGA')) {
                $datos['ULTIMA_DESCARGA'] = $request->ULTIMA_DESCARGA;
            }
            
            if ($request->has('ULTIMA_DESCARGA_BUENA')) {
                $datos['ULTIMA_DESCARGA_BUENA'] = $request->ULTIMA_DESCARGA_BUENA;
            }
            
            $result = $reloj->editarReloj($id, $datos);
            
            DB::commit();
            return response()->json($result, 200);
        } catch (\Illuminate\Database\QueryException $err) {
            DB::rollback();
            if ($err->getCode() == 23000) {
                return response()->json(['message' => 'La IP ya existe en la base de datos'], 400);
            }
            return response()->json(['message' => $err->getMessage()], 500);
        } catch (Exception $err) {
            DB::rollback();
            return response()->json(['message' => $err->getMessage()], $err->getCode() ?: 500);
        }
    }

    /**
     * Eliminar reloj (cambiar ACTIVO a 0)
     */
    public function eliminarReloj($id)
    {
        try {
            $reloj = new Reloj;
            $reloj->eliminarReloj($id);
            
            return response()->json(['message' => 'Reloj eliminado exitosamente'], 200);
        } catch (Exception $err) {
            return response()->json(['message' => $err->getMessage()], $err->getCode() ?: 500);
        }
    }
}