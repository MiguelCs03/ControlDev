<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Auth;

class Nivel extends Model
{
    protected $table = 'nivel';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'categoria',
        'minimo',
        'midpoint',
        'maximo',
        'activo',
        'creado_por',
        'creado_el',
        'modificado_por',
        'modificado_el'
    ];

    protected $casts = [
        'id' => 'integer',
        'minimo' => 'integer',
        'midpoint' => 'integer',
        'maximo' => 'integer',
        'activo' => 'boolean',
        'creado_por' => 'integer',
        'modificado_por' => 'integer',
        'creado_el' => 'datetime',
        'modificado_el' => 'datetime',
    ];

    /**
     * Función para listar todos los niveles ACTIVOS con cálculos
     */
    public function listarNiveles($search = null)
    {
        try {
            DB::beginTransaction();
            
            $query = $this->where('activo', 1);
            
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('nombre', 'LIKE', "%{$search}%")
                      ->orWhere('categoria', 'LIKE', "%{$search}%");
                });
            }
            
            $resultado = $query->orderBy('midpoint', 'desc')->get();
            
            // Agregar cálculos a cada resultado
            $resultado->transform(function($item) {
                $midpoint = (int)$item->midpoint;
                
                // Calcular porcentajes como en el PHP antiguo
                $item->ochenta = round($midpoint * 0.80);
                $item->noventa = round($midpoint * 0.90);
                $item->ciento_diez = round($midpoint * 1.10);
                $item->ciento_veinte = round($midpoint * 1.20);
                
                return $item;
            });
            
            DB::commit();
            return $resultado;
        } catch (Exception $err) {
            DB::rollback();
            throw $err;
        }
    }

    /**
     * Función para crear un nuevo nivel
     */
    public function crearNivel($datos)
    {
        try {
            DB::beginTransaction();
            
            // Setear campos por defecto
            $datos['activo'] = 1;
            $datos['creado_el'] = now();
            
            if (!isset($datos['minimo'])) {
                // Opción A: Poner en 0
                $datos['minimo'] = 0;
            }
            
            if (!isset($datos['maximo'])) {
                // Opción A: Poner en 0
                $datos['maximo'] = 0;
            }
            
            // Si no viene CREADO_POR, intentar obtenerlo de Auth
            if (!isset($datos['creado_por']) && Auth::check()) {
                $datos['creado_por'] = Auth::id();
            }
            
            // Verificar que el nombre sea único
            $existe = $this->where('nombre', $datos['nombre'])->exists();
            if ($existe) {
                throw new Exception("Ya existe un nivel con este nombre", 409);
            }
            
            // INSERT en la base de datos
            $nivel = $this->create($datos);
            
            DB::commit();
            return $nivel;
        } catch (Exception $err) {
            DB::rollback();
            throw $err;
        }
    }

    /**
     * Función para editar el nivel (solo activos)
     */
    public function editarNivel($id, $datos)
    {
        try {
            DB::beginTransaction();
            
            $nivel = $this->where('activo', 1)->find($id);
            if (!$nivel) {
                throw new Exception("Nivel no encontrado", 404);
            }
            
            // Verificar que el nuevo nombre no exista (si se está cambiando)
            if (isset($datos['nombre']) && $datos['nombre'] !== $nivel->nombre) {
                $existe = $this->where('nombre', $datos['nombre'])->where('id', '!=', $id)->exists();
                if ($existe) {
                    throw new Exception("Ya existe un nivel con este nombre", 409);
                }
            }
            
            $datos['modificado_el'] = now();
            
            // Si no viene MODIFICADO_POR, intentar obtenerlo de Auth
            if (!isset($datos['modificado_por']) && Auth::check()) {
                $datos['modificado_por'] = Auth::id();
            }
            
            // UPDATE en la base de datos
            $nivel->update($datos);
            
            DB::commit();
            return $nivel;
        } catch (Exception $err) {
            DB::rollback();
            throw $err;
        }
    }

    /**
     * Función para ELIMINAR nivel (poner ACTIVO = 0)
     */
    public function eliminarNivel($id)
    {
        try {
            DB::beginTransaction();
            
            $nivel = $this->where('activo', 1)->find($id);
            if (!$nivel) {
                throw new Exception("Nivel no encontrado", 404);
            }
            
            // Desactivar permanentemente
            $nivel->activo = 0;
            $nivel->modificado_el = now();
            
            // Agregar usuario que elimina
            if (Auth::check()) {
                $nivel->modificado_por = Auth::id();
            }
            
            $nivel->save();
            
            DB::commit();
            return $nivel;
        } catch (Exception $err) {
            DB::rollback();
            throw $err;
        }
    }
}