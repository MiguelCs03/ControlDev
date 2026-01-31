<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Auth;

class Unidad extends Model
{
    protected $table = 'unidad';
    protected $primaryKey = 'COD_UNIDAD';
    public $timestamps = false;

    protected $fillable = [
        'DESCRIPCION',
        'COD_UNIDAD_BD',
        'ACTIVO',
        'CREADO_POR',
        'CREADO_EL',
        'MODIFICADO_POR',
        'MODIFICADO_EL'
    ];

    protected $casts = [
        'COD_UNIDAD' => 'integer',
        'COD_UNIDAD_BD' => 'integer',
        'ACTIVO' => 'boolean',
        'CREADO_EL' => 'datetime',
        'MODIFICADO_EL' => 'datetime',
    ];

    /**
     * Función para listar todas las unidades ACTIVAS
     */
    public function listarUnidades($search = null)
    {
        try {
            DB::beginTransaction();
            
            $query = $this->where('ACTIVO', 1);
            
            if ($search) {
                $query->where('DESCRIPCION', 'LIKE', "%{$search}%");
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
     * Función para crear una nueva unidad
     */
    public function crearUnidad($datos)
    {
        try {
            DB::beginTransaction();
            
            // Setear campos por defecto - SIEMPRE activa al crear
            $datos['ACTIVO'] = 1;
            $datos['CREADO_EL'] = now();
            
            // COD_UNIDAD_BD por defecto 0 si no viene
            if (!isset($datos['COD_UNIDAD_BD'])) {
                $datos['COD_UNIDAD_BD'] = 0;
            }
            
            // Si no viene CREADO_POR, intentar obtenerlo de Auth
            if (!isset($datos['CREADO_POR']) && Auth::check()) {
                $datos['CREADO_POR'] = Auth::id();
            }
            
            // INSERT en la base de datos
            $unidad = $this->create($datos);
            
            DB::commit();
            return $unidad;
        } catch (Exception $err) {
            DB::rollback();
            throw $err;
        }
    }

    /**
     * Función para editar la unidad (solo activas)
     */
    public function editarUnidad($id, $datos)
    {
        try {
            DB::beginTransaction();
            
            $unidad = $this->where('ACTIVO', 1)->find($id);
            if (!$unidad) {
                throw new Exception("Unidad no encontrada", 404);
            }
            
            $datos['MODIFICADO_EL'] = now();
            
            // Si no viene MODIFICADO_POR, intentar obtenerlo de Auth
            if (!isset($datos['MODIFICADO_POR']) && Auth::check()) {
                $datos['MODIFICADO_POR'] = Auth::id();
            }
            
            // UPDATE en la base de datos
            $unidad->update($datos);
            
            DB::commit();
            return $unidad;
        } catch (Exception $err) {
            DB::rollback();
            throw $err;
        }
    }

    /**
     * Función para ELIMINAR unidad (poner ACTIVO = 0)
     */
    public function eliminarUnidad($id)
    {
        try {
            DB::beginTransaction();
            
            $unidad = $this->where('ACTIVO', 1)->find($id);
            if (!$unidad) {
                throw new Exception("Unidad no encontrada", 404);
            }
            
            // Desactivar permanentemente
            $unidad->ACTIVO = 0;
            $unidad->MODIFICADO_EL = now();
            
            // Agregar usuario que elimina
            if (Auth::check()) {
                $unidad->MODIFICADO_POR = Auth::id();
            }
            
            $unidad->save();
            
            DB::commit();
            return $unidad;
        } catch (Exception $err) {
            DB::rollback();
            throw $err;
        }
    }
}