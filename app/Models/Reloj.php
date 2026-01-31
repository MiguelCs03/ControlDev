<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Auth;

class Reloj extends Model
{
    protected $table = 'reloj';
    protected $primaryKey = 'COD_RELOJ';
    public $timestamps = false;

    protected $fillable = [
        'NOMBRE',
        'IP',
        'OC1',
        'OC2',
        'OC3',
        'OC4',
        'ESTADO',
        'ULTIMA_DESCARGA',
        'ULTIMA_DESCARGA_BUENA',
        'ACTIVO',
        'CREADO_POR',
        'CREADO_EL',
        'MODIFICADO_POR',
        'MODIFICADO_EL'
    ];

    protected $casts = [
        'COD_RELOJ' => 'integer',
        'OC1' => 'integer',
        'OC2' => 'integer',
        'OC3' => 'integer',
        'OC4' => 'integer',
        'ESTADO' => 'integer',
        'ACTIVO' => 'boolean',
        'ULTIMA_DESCARGA' => 'datetime',
        'ULTIMA_DESCARGA_BUENA' => 'datetime',
        'CREADO_EL' => 'datetime',
        'MODIFICADO_EL' => 'datetime',
    ];

    /**
     * Función para listar relojes activos
     */
    public function listarActivos($search = null)
    {
        try {
            DB::beginTransaction();
            
            $query = $this->where('ACTIVO', 1);
            
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('NOMBRE', 'LIKE', "%{$search}%")
                      ->orWhere('IP', 'LIKE', "%{$search}%");
                });
            }
            
            $resultado = $query->orderBy('NOMBRE')->get();
            
            DB::commit();
            return $resultado;
        } catch (Exception $err) {
            DB::rollback();
            throw $err;
        }
    }

    /**
     * Función para crear un nuevo reloj
     */
    public function crearReloj($datos)
    {
        try {
            DB::beginTransaction();
            
            // CONCATENAR los OC para formar la IP completa
            $datos['IP'] = $datos['OC1'] . '.' . $datos['OC2'] . '.' . 
                           $datos['OC3'] . '.' . $datos['OC4'];
            
            // Setear campos por defecto
            $datos['ACTIVO'] = 1;
            $datos['CREADO_EL'] = now();
            
            // Si no viene CREADO_POR, intentar obtenerlo de Auth
            if (!isset($datos['CREADO_POR']) && Auth::check()) {
                $datos['CREADO_POR'] = Auth::id();
            }
            
            // INSERT en la base de datos
            $reloj = $this->create($datos);
            
            DB::commit();
            return $reloj;
        } catch (Exception $err) {
            DB::rollback();
            throw $err;
        }
    }

    /**
     * Función para editar el reloj
     */
    public function editarReloj($id, $datos)
    {
        try {
            DB::beginTransaction();
            
            $reloj = $this->find($id);
            if (!$reloj) {
                throw new Exception("Reloj no encontrado", 404);
            }
            
            // Si se actualizan los OC, regenerar la IP
            if (isset($datos['OC1']) || isset($datos['OC2']) || 
                isset($datos['OC3']) || isset($datos['OC4'])) {
                
                $oc1 = $datos['OC1'] ?? $reloj->OC1;
                $oc2 = $datos['OC2'] ?? $reloj->OC2;
                $oc3 = $datos['OC3'] ?? $reloj->OC3;
                $oc4 = $datos['OC4'] ?? $reloj->OC4;
                
                $datos['IP'] = $oc1 . '.' . $oc2 . '.' . $oc3 . '.' . $oc4;
            }
            
            $datos['MODIFICADO_EL'] = now();
            
            // Si no viene MODIFICADO_POR, intentar obtenerlo de Auth
            if (!isset($datos['MODIFICADO_POR']) && Auth::check()) {
                $datos['MODIFICADO_POR'] = Auth::id();
            }
            
            // UPDATE en la base de datos
            $reloj->update($datos);
            
            DB::commit();
            return $reloj;
        } catch (Exception $err) {
            DB::rollback();
            throw $err;
        }
    }

    /**
     * Función para eliminar el reloj (cambiar ACTIVO a 0)
     */
    public function eliminarReloj($id)
    {
        try {
            DB::beginTransaction();
            
            $reloj = $this->find($id);
            if (!$reloj) {
                throw new Exception("Reloj no encontrado", 404);
            }
            
            $reloj->ACTIVO = 0;
            $reloj->MODIFICADO_EL = now();
            
            // Agregar usuario que elimina
            if (Auth::check()) {
                $reloj->MODIFICADO_POR = Auth::id();
            }
            
            // UPDATE en la base de datos (cambia ACTIVO a 0)
            $reloj->save();
            
            DB::commit();
            return true;
        } catch (Exception $err) {
            DB::rollback();
            throw $err;
        }
    }
}