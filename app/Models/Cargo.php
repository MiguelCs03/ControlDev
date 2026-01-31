<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Exception;

class Cargo extends Model
{
    protected $table = 'cargo';
    protected $primaryKey = 'COD_CARGO';
    public $timestamps = false;

    protected $fillable = [
        'DESCRIPCION',
        'ACTIVO',
        'CREADO_POR',
        'CREADO_EL',
        'MODIFICADO_POR',
        'MODIFICADO_EL'
    ];

    protected $casts = [
        'COD_CARGO' => 'integer',
        'ACTIVO' => 'boolean',
        'CREADO_EL' => 'datetime',
        'MODIFICADO_EL' => 'datetime',
    ];

    /**
     * Función para listar todos los cargos ACTIVOS
     */
    public function listarCargos($search = null)
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
     * Función para crear un nuevo cargo
     */
    public function crearCargo($datos)
    {
        try {
            DB::beginTransaction();
            
            // Setear campos por defecto - SIEMPRE activo al crear
            $datos['ACTIVO'] = 1;
            $datos['CREADO_EL'] = now();
            
            $cargo = $this->create($datos);
            
            DB::commit();
            return $cargo;
        } catch (Exception $err) {
            DB::rollback();
            throw $err;
        }
    }

    /**
     * Función para editar el cargo
     */
    public function editarCargo($id, $datos)
    {
        try {
            DB::beginTransaction();
            
            $cargo = $this->where('ACTIVO', 1)->find($id);
            if (!$cargo) {
                throw new Exception("Cargo no encontrado", 404);
            }
            
            $datos['MODIFICADO_EL'] = now();
            $cargo->update($datos);
            
            DB::commit();
            return $cargo;
        } catch (Exception $err) {
            DB::rollback();
            throw $err;
        }
    }

    /**
     * Función para ELIMINAR (desactivar) el cargo
     * Pone ACTIVO = 0 y no se puede reactivar
     */
    public function eliminarCargo($id)
    {
        try {
            DB::beginTransaction();
            
            $cargo = $this->where('ACTIVO', 1)->find($id);
            if (!$cargo) {
                throw new Exception("Cargo no encontrado", 404);
            }
            
            // Desactivar permanentemente
            $cargo->ACTIVO = 0;
            $cargo->MODIFICADO_EL = now();
            $cargo->save();
            
            DB::commit();
            return $cargo;
        } catch (Exception $err) {
            DB::rollback();
            throw $err;
        }
    }
}