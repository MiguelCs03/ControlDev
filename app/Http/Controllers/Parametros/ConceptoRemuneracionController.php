<?php

namespace App\Http\Controllers\Parametros;

use App\Http\Controllers\Controller;
use App\Models\ConceptoRemuneracion;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ConceptoRemuneracionController extends Controller
{
    /**
     * Listar todos los conceptos ACTIVOS
     */
    public function listarConceptos(Request $request)
    {
        try {
            $concepto = new ConceptoRemuneracion;
            $result = $concepto->listarConceptos($request->search);
            
            return response()->json($result, 200);
        } catch (Exception $err) {
            return response()->json($err->getMessage(), $err->getCode() ?: 500);
        }
    }

    /**
     * Crear un nuevo concepto
     */
    public function crearConcepto(Request $request)
    {
        try {
            DB::beginTransaction();
            
            $request->validate([
                'nombre' => 'required|string|max:200',
                'tipo' => 'required|in:1,2,3',
                'tipo_monto' => 'required|in:1,2',
                'monto' => 'required|numeric|min:0',
                'porcentaje_de' => 'nullable|in:1,2,3,4',
            ]);
            
            $concepto = new ConceptoRemuneracion;
            
            $datos = [
                'nombre' => $request->nombre,
                'tipo' => $request->tipo,
                'tipo_monto' => $request->tipo_monto,
                'monto' => $request->monto,
                'porcentaje_de' => $request->porcentaje_de,
            ];
            
            // Agregar usuario que crea
            if (Auth::check()) {
                $datos['creado_por'] = Auth::id();
            }
            
            $result = $concepto->crearConcepto($datos);
            
            DB::commit();
            return response()->json($result, 201);
        } catch (Exception $err) {
            DB::rollback();
            return response()->json($err->getMessage(), $err->getCode() ?: 500);
        }
    }

    /**
     * Editar concepto existente (solo activos)
     */
    public function editarConcepto(Request $request, $id)
    {
        try {
            DB::beginTransaction();
            
            $request->validate([
                'nombre' => 'required|string|max:200',
                'tipo' => 'required|in:1,2,3',
                'tipo_monto' => 'required|in:1,2',
                'monto' => 'required|numeric|min:0',
                'porcentaje_de' => 'nullable|in:1,2,3,4',
            ]);
            
            $concepto = new ConceptoRemuneracion;
            
            $datos = [
                'nombre' => $request->nombre,
                'tipo' => $request->tipo,
                'tipo_monto' => $request->tipo_monto,
                'monto' => $request->monto,
                'porcentaje_de' => $request->porcentaje_de,
            ];
            
            // Agregar usuario que modifica
            if (Auth::check()) {
                $datos['modificado_por'] = Auth::id();
            }
            
            $result = $concepto->editarConcepto($id, $datos);
            
            DB::commit();
            return response()->json($result, 200);
        } catch (Exception $err) {
            DB::rollback();
            return response()->json($err->getMessage(), $err->getCode() ?: 500);
        }
    }

    /**
     * ELIMINAR concepto (poner ACTIVO = 0)
     */
    public function eliminarConcepto($id)
    {
        try {
            $concepto = new ConceptoRemuneracion;
            $result = $concepto->eliminarConcepto($id);
            
            return response()->json([
                'message' => "Concepto eliminado exitosamente",
                'data' => $result
            ], 200);
        } catch (Exception $err) {
            return response()->json($err->getMessage(), $err->getCode() ?: 500);
        }
    }

    /**
     * Obtener opciones para los selects
     */
    public function obtenerOpciones()
    {
        try {
            $concepto = new ConceptoRemuneracion;
            
            return response()->json([
                'tipos' => $concepto->getTipoLabels(),
                'tipos_monto' => $concepto->getTipoMontoLabels(),
                'porcentajes_de' => $concepto->getPorcentajeDeLabels(),
            ], 200);
        } catch (Exception $err) {
            return response()->json($err->getMessage(), $err->getCode() ?: 500);
        }
    }
}