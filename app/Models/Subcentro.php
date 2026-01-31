<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Auth;

class Subcentro extends Model
{
    protected $table = 'subcentro';
    protected $primaryKey = 'COD_SUBCENTRO';
    public $timestamps = false;

    protected $fillable = [
        'DESCRIPCION',
        'COD_SUBCENTRO_BD',
        'ACTIVO',
        'CREADO_POR',
        'CREADO_EL',
        'MODIFICADO_POR',
        'MODIFICADO_EL'
    ];

    protected $casts = [
        'COD_SUBCENTRO' => 'integer',
        'COD_SUBCENTRO_BD' => 'integer',
        'ACTIVO' => 'boolean',
        'CREADO_EL' => 'datetime',
        'MODIFICADO_EL' => 'datetime',
    ];

    /**
     * Función para listar todos los subcentros ACTIVOS
     */
    public function listarSubcentros($search = null)
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
     * Función para crear un nuevo subcentro
     */
    public function crearSubcentro($datos)
    {
        try {
            DB::beginTransaction();
            
            // Setear campos por defecto - SIEMPRE activo al crear
            $datos['ACTIVO'] = 1;
            $datos['CREADO_EL'] = now();
            
            // COD_SUBCENTRO_BD por defecto 0 si no viene
            if (!isset($datos['COD_SUBCENTRO_BD'])) {
                $datos['COD_SUBCENTRO_BD'] = 0;
            }
            
            // Si no viene CREADO_POR, intentar obtenerlo de Auth
            if (!isset($datos['CREADO_POR']) && Auth::check()) {
                $datos['CREADO_POR'] = Auth::id();
            }
            
            // INSERT en la base de datos
            $subcentro = $this->create($datos);
            
            DB::commit();
            return $subcentro;
        } catch (Exception $err) {
            DB::rollback();
            throw $err;
        }
    }

    /**
     * Función para editar el subcentro (solo activos)
     */
    public function editarSubcentro($id, $datos)
    {
        try {
            DB::beginTransaction();
            
            $subcentro = $this->where('ACTIVO', 1)->find($id);
            if (!$subcentro) {
                throw new Exception("Subcentro no encontrado", 404);
            }
            
            $datos['MODIFICADO_EL'] = now();
            
            // Si no viene MODIFICADO_POR, intentar obtenerlo de Auth
            if (!isset($datos['MODIFICADO_POR']) && Auth::check()) {
                $datos['MODIFICADO_POR'] = Auth::id();
            }
            
            // UPDATE en la base de datos
            $subcentro->update($datos);
            
            DB::commit();
            return $subcentro;
        } catch (Exception $err) {
            DB::rollback();
            throw $err;
        }
    }

    /**
     * Función para ELIMINAR subcentro (poner ACTIVO = 0)
     */
    public function eliminarSubcentro($id)
    {
        try {
            DB::beginTransaction();
            
            $subcentro = $this->where('ACTIVO', 1)->find($id);
            if (!$subcentro) {
                throw new Exception("Subcentro no encontrado", 404);
            }
            
            // Desactivar permanentemente
            $subcentro->ACTIVO = 0;
            $subcentro->MODIFICADO_EL = now();
            
            // Agregar usuario que elimina
            if (Auth::check()) {
                $subcentro->MODIFICADO_POR = Auth::id();
            }
            
            $subcentro->save();
            
            DB::commit();
            return $subcentro;
        } catch (Exception $err) {
            DB::rollback();
            throw $err;
        }
    }
}