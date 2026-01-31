<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Auth;

class ConceptoRemuneracion extends Model
{
    protected $table = 'z_conceptos_remuneracion';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'tipo',
        'tipo_monto',
        'monto',
        'porcentaje_de',
        'activo',
        'creado_por',
        'creado_el',
        'modificado_por',
        'modificado_el',
        'eliminado_por',
        'eliminado_el'
    ];

    protected $casts = [
        'id' => 'integer',
        'tipo' => 'integer',
        'tipo_monto' => 'integer',
        'monto' => 'decimal:5',
        'porcentaje_de' => 'integer',
        'activo' => 'boolean',
        'creado_por' => 'integer',
        'modificado_por' => 'integer',
        'eliminado_por' => 'integer',
        'creado_el' => 'datetime',
        'modificado_el' => 'datetime',
        'eliminado_el' => 'datetime',
    ];

    // Constantes para tipos
    const TIPO_INGRESO = 1;
    const TIPO_EGRESO = 2;
    const TIPO_CARGAS_SOCIALES = 3;

    // Constantes para tipo monto
    const TIPO_MONTO_PORCENTAJE = 1;
    const TIPO_MONTO_FIJO = 2;

    // Constantes para porcentaje 
    const PORCENTAJE_DE_HABER_BASICO = 1;
    const PORCENTAJE_DE_TOTAL_GANADO = 2;

    /**
     * Get tipo labels
     */
    public static function getTipoLabels()
    {
        return [
            self::TIPO_INGRESO => 'INGRESO',
            self::TIPO_EGRESO => 'EGRESO',
            self::TIPO_CARGAS_SOCIALES => 'CARGAS SOCIALES',
        ];
    }

    /**
     * Get tipo monto labels
     */
    public static function getTipoMontoLabels()
    {
        return [
            self::TIPO_MONTO_PORCENTAJE => '%',
            self::TIPO_MONTO_FIJO => 'FIJO',
        ];
    }

    /**
     * Get porcentaje de labels (CORREGIDO - SOLO 2 OPCIONES)
     */
    public static function getPorcentajeDeLabels()
    {
        return [
            self::PORCENTAJE_DE_HABER_BASICO => 'HABER BASICO',
            self::PORCENTAJE_DE_TOTAL_GANADO => 'TOTAL GANADO',
        ];
    }

    /**
     * Función para listar todos los conceptos ACTIVOS
     */
    public function listarConceptos($search = null)
    {
        try {
            DB::beginTransaction();
            
            $query = $this->where('activo', 1);
            
            if ($search) {
                $query->where('nombre', 'LIKE', "%{$search}%");
            }
            
            $resultado = $query->orderBy('nombre')->get();
            
            DB::commit();
            return $resultado;
        } catch (Exception $err) {
            DB::rollback();
            throw $err;
        }
    }

    /**
     * Función para crear un nuevo concepto
     */
    public function crearConcepto($datos)
    {
        try {
            DB::beginTransaction();
            
            // Setear campos por defecto
            $datos['activo'] = 1;
            $datos['creado_el'] = now();
            
            // Si el tipo_monto es PORCENTAJE, validar que porcentaje_de no sea nulo
            if ($datos['tipo_monto'] == self::TIPO_MONTO_PORCENTAJE && !isset($datos['porcentaje_de'])) {
                throw new Exception("Debe especificar el tipo de porcentaje (HABER BASICO o TOTAL GANADO)", 400);
            }
            
            // Si el tipo_monto es FIJO, porcentaje_de debe ser null
            if ($datos['tipo_monto'] == self::TIPO_MONTO_FIJO) {
                $datos['porcentaje_de'] = null;
            }
            
            // Si no viene CREADO_POR, intentar obtenerlo de Auth
            if (!isset($datos['creado_por']) && Auth::check()) {
                $datos['creado_por'] = Auth::id();
            }
            
            // Verificar que el nombre sea único
            $existe = $this->where('nombre', $datos['nombre'])->exists();
            if ($existe) {
                throw new Exception("Ya existe un concepto con este nombre", 409);
            }
            
            // INSERT en la base de datos
            $concepto = $this->create($datos);
            
            DB::commit();
            return $concepto;
        } catch (Exception $err) {
            DB::rollback();
            throw $err;
        }
    }

    /**
     * Función para editar el concepto (solo activos)
     */
    public function editarConcepto($id, $datos)
    {
        try {
            DB::beginTransaction();
            
            $concepto = $this->where('activo', 1)->find($id);
            if (!$concepto) {
                throw new Exception("Concepto no encontrado", 404);
            }
            
            // Si el tipo_monto es PORCENTAJE, validar que porcentaje_de no sea nulo
            if ($datos['tipo_monto'] == self::TIPO_MONTO_PORCENTAJE && !isset($datos['porcentaje_de'])) {
                throw new Exception("Debe especificar el tipo de porcentaje (HABER BASICO o TOTAL GANADO)", 400);
            }
            
            // Si el tipo_monto es FIJO, porcentaje_de debe ser null
            if ($datos['tipo_monto'] == self::TIPO_MONTO_FIJO) {
                $datos['porcentaje_de'] = null;
            }
            
            // Verificar que el nuevo nombre no exista (si se está cambiando)
            if (isset($datos['nombre']) && $datos['nombre'] !== $concepto->nombre) {
                $existe = $this->where('nombre', $datos['nombre'])->where('id', '!=', $id)->exists();
                if ($existe) {
                    throw new Exception("Ya existe un concepto con este nombre", 409);
                }
            }
            
            $datos['modificado_el'] = now();
            
            // Si no viene MODIFICADO_POR, intentar obtenerlo de Auth
            if (!isset($datos['modificado_por']) && Auth::check()) {
                $datos['modificado_por'] = Auth::id();
            }
            
            // UPDATE en la base de datos
            $concepto->update($datos);
            
            DB::commit();
            return $concepto;
        } catch (Exception $err) {
            DB::rollback();
            throw $err;
        }
    }

    /**
     * Función para ELIMINAR concepto (poner ACTIVO = 0)
     */
    public function eliminarConcepto($id)
    {
        try {
            DB::beginTransaction();
            
            $concepto = $this->where('activo', 1)->find($id);
            if (!$concepto) {
                throw new Exception("Concepto no encontrado", 404);
            }
            
            // Desactivar permanentemente
            $concepto->activo = 0;
            $concepto->eliminado_el = now();
            
            // Agregar usuario que elimina
            if (Auth::check()) {
                $concepto->eliminado_por = Auth::id();
            }
            
            $concepto->save();
            
            DB::commit();
            return $concepto;
        } catch (Exception $err) {
            DB::rollback();
            throw $err;
        }
    }
}