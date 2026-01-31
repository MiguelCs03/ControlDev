<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Auth;

class TipoJustificacion extends Model
{
    protected $table = 'tipo_justificacion';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'nombre',
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
        'activo' => 'boolean',
        'creado_por' => 'integer',
        'modificado_por' => 'integer',
        'eliminado_por' => 'integer',
        'creado_el' => 'datetime',
        'modificado_el' => 'datetime',
        'eliminado_el' => 'datetime',
    ];

    /**
     * Función para listar todos los tipos de justificación ACTIVOS
     */
    public function listarTiposJustificacion($search = null)
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
     * Función para crear un nuevo tipo de justificación
     */
    public function crearTipoJustificacion($datos)
    {
        try {
            DB::beginTransaction();
            
            // Setear campos por defecto
            $datos['activo'] = 1;
            $datos['creado_el'] = now();
            
            // Si no viene CREADO_POR, intentar obtenerlo de Auth
            if (!isset($datos['creado_por']) && Auth::check()) {
                $datos['creado_por'] = Auth::id();
            }
            
            // Verificar que el nombre sea único
            $existe = $this->where('nombre', $datos['nombre'])->exists();
            if ($existe) {
                throw new Exception("Ya existe un tipo de justificación con este nombre", 409);
            }
            
            // INSERT en la base de datos
            $tipoJustificacion = $this->create($datos);
            
            DB::commit();
            return $tipoJustificacion;
        } catch (Exception $err) {
            DB::rollback();
            throw $err;
        }
    }

    /**
     * Función para editar el tipo de justificación (solo activos)
     */
    public function editarTipoJustificacion($id, $datos)
    {
        try {
            DB::beginTransaction();
            
            $tipoJustificacion = $this->where('activo', 1)->find($id);
            if (!$tipoJustificacion) {
                throw new Exception("Tipo de justificación no encontrado", 404);
            }
            
            // Verificar que el nuevo nombre no exista (si se está cambiando)
            if (isset($datos['nombre']) && $datos['nombre'] !== $tipoJustificacion->nombre) {
                $existe = $this->where('nombre', $datos['nombre'])->where('id', '!=', $id)->exists();
                if ($existe) {
                    throw new Exception("Ya existe un tipo de justificación con este nombre", 409);
                }
            }
            
            $datos['modificado_el'] = now();
            
            // Si no viene MODIFICADO_POR, intentar obtenerlo de Auth
            if (!isset($datos['modificado_por']) && Auth::check()) {
                $datos['modificado_por'] = Auth::id();
            }
            
            // UPDATE en la base de datos
            $tipoJustificacion->update($datos);
            
            DB::commit();
            return $tipoJustificacion;
        } catch (Exception $err) {
            DB::rollback();
            throw $err;
        }
    }

    /**
     * Función para ELIMINAR tipo de justificación (poner ACTIVO = 0)
     */
    public function eliminarTipoJustificacion($id)
    {
        try {
            DB::beginTransaction();
            
            $tipoJustificacion = $this->where('activo', 1)->find($id);
            if (!$tipoJustificacion) {
                throw new Exception("Tipo de justificación no encontrado", 404);
            }
            
            // Desactivar permanentemente
            $tipoJustificacion->activo = 0;
            $tipoJustificacion->eliminado_el = now();
            
            // Agregar usuario que elimina
            if (Auth::check()) {
                $tipoJustificacion->eliminado_por = Auth::id();
            }
            
            $tipoJustificacion->save();
            
            DB::commit();
            return $tipoJustificacion;
        } catch (Exception $err) {
            DB::rollback();
            throw $err;
        }
    }
}