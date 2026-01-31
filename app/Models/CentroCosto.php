<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Auth;

class CentroCosto extends Model
{
    protected $table = 'centro_de_costo';
    protected $primaryKey = 'COD_CENTRO';
    public $timestamps = false;

    protected $fillable = [
        'DESCRIPCION',
        'COD_SUBCENTRO',
        'COD_UNIDAD',
        'COD_CENTRO_BD',
        'codigo_base',
        'ACTIVO',
        'CREADO_POR',
        'CREADO_EL',
        'MODIFICADO_POR',
        'MODIFICADO_EL'
    ];

    protected $casts = [
        'COD_CENTRO' => 'integer',
        'COD_SUBCENTRO' => 'integer',
        'COD_UNIDAD' => 'integer',
        'COD_CENTRO_BD' => 'integer',
        'codigo_base' => 'integer',
        'ACTIVO' => 'boolean',
        'CREADO_EL' => 'datetime',
        'MODIFICADO_EL' => 'datetime',
    ];

    // Relaciones
    public function subcentro()
    {
        return $this->belongsTo(Subcentro::class, 'COD_SUBCENTRO', 'COD_SUBCENTRO');
    }

    public function unidad()
    {
        return $this->belongsTo(Unidad::class, 'COD_UNIDAD', 'COD_UNIDAD');
    }

    /**
     * Función para listar todos los centros de costo ACTIVOS con relaciones
     */
    public function listarCentrosCosto($search = null)
    {
        try {
            DB::beginTransaction();
            
            $query = $this->with(['subcentro', 'unidad'])
                ->where('ACTIVO', 1);
            
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('DESCRIPCION', 'LIKE', "%{$search}%")
                      ->orWhere('codigo_base', 'LIKE', "%{$search}%")
                      ->orWhereHas('subcentro', function($q2) use ($search) {
                          $q2->where('DESCRIPCION', 'LIKE', "%{$search}%");
                      })
                      ->orWhereHas('unidad', function($q2) use ($search) {
                          $q2->where('DESCRIPCION', 'LIKE', "%{$search}%");
                      });
                });
            }
            
            $resultado = $query->orderBy('DESCRIPCION')->get();
            
            DB::commit();
            return $resultado;
        } catch (Exception $err) {
            DB::rollback();
            throw $err;
        }
    }

    /**
     * Función para crear un nuevo centro de costo
     */
    public function crearCentroCosto($datos)
    {
        try {
            DB::beginTransaction();
            
            // Setear campos por defecto - SIEMPRE activo al crear
            $datos['ACTIVO'] = 1;
            $datos['CREADO_EL'] = now();
            
            // COD_CENTRO_BD por defecto 0 - NO se pasa desde el frontend
            $datos['COD_CENTRO_BD'] = 0;
            
            // Si no viene CREADO_POR, intentar obtenerlo de Auth
            if (!isset($datos['CREADO_POR']) && Auth::check()) {
                $datos['CREADO_POR'] = Auth::id();
            }
            
            // Validar que existan el subcentro y la unidad
            $subcentro = Subcentro::where('ACTIVO', 1)->find($datos['COD_SUBCENTRO']);
            if (!$subcentro) {
                throw new Exception("Subcentro no encontrado o inactivo", 404);
            }
            
            $unidad = Unidad::where('ACTIVO', 1)->find($datos['COD_UNIDAD']);
            if (!$unidad) {
                throw new Exception("Unidad no encontrada o inactiva", 404);
            }
            
            // INSERT en la base de datos
            $centroCosto = $this->create($datos);
            
            // Cargar relaciones
            $centroCosto->load(['subcentro', 'unidad']);
            
            DB::commit();
            return $centroCosto;
        } catch (Exception $err) {
            DB::rollback();
            throw $err;
        }
    }

    /**
     * Función para editar el centro de costo (solo activos)
     */
    public function editarCentroCosto($id, $datos)
    {
        try {
            DB::beginTransaction();
            
            $centroCosto = $this->where('ACTIVO', 1)->find($id);
            if (!$centroCosto) {
                throw new Exception("Centro de costo no encontrado", 404);
            }
            
            $datos['MODIFICADO_EL'] = now();
            
            // Si no viene MODIFICADO_POR, intentar obtenerlo de Auth
            if (!isset($datos['MODIFICADO_POR']) && Auth::check()) {
                $datos['MODIFICADO_POR'] = Auth::id();
            }
            
            // NO permitir cambiar COD_CENTRO_BD desde el frontend
            if (isset($datos['COD_CENTRO_BD'])) {
                unset($datos['COD_CENTRO_BD']);
            }
            
            // Validar que existan el subcentro y la unidad si se están cambiando
            if (isset($datos['COD_SUBCENTRO'])) {
                $subcentro = Subcentro::where('ACTIVO', 1)->find($datos['COD_SUBCENTRO']);
                if (!$subcentro) {
                    throw new Exception("Subcentro no encontrado o inactivo", 404);
                }
            }
            
            if (isset($datos['COD_UNIDAD'])) {
                $unidad = Unidad::where('ACTIVO', 1)->find($datos['COD_UNIDAD']);
                if (!$unidad) {
                    throw new Exception("Unidad no encontrada o inactiva", 404);
                }
            }
            
            // UPDATE en la base de datos
            $centroCosto->update($datos);
            
            // Cargar relaciones actualizadas
            $centroCosto->load(['subcentro', 'unidad']);
            
            DB::commit();
            return $centroCosto;
        } catch (Exception $err) {
            DB::rollback();
            throw $err;
        }
    }

    /**
     * Función para ELIMINAR centro de costo (poner ACTIVO = 0)
     */
    public function eliminarCentroCosto($id)
    {
        try {
            DB::beginTransaction();
            
            $centroCosto = $this->where('ACTIVO', 1)->find($id);
            if (!$centroCosto) {
                throw new Exception("Centro de costo no encontrado", 404);
            }
            
            // Desactivar permanentemente
            $centroCosto->ACTIVO = 0;
            $centroCosto->MODIFICADO_EL = now();
            
            // Agregar usuario que elimina
            if (Auth::check()) {
                $centroCosto->MODIFICADO_POR = Auth::id();
            }
            
            $centroCosto->save();
            
            DB::commit();
            return $centroCosto;
        } catch (Exception $err) {
            DB::rollback();
            throw $err;
        }
    }
}