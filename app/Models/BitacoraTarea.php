<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BitacoraTarea extends Model
{
    use HasFactory;

    // Desactivar timestamps automáticos
    public $timestamps = false;

    protected $table = 'bitacora_tareas';

    protected $fillable = [
        'tarea_id',
        'usuario_id',
        'accion',
        'descripcion',
        'metadata',
        'creado_en',
    ];

    protected $casts = [
        'metadata' => 'array',
        'creado_en' => 'datetime',
    ];

    // Relaciones
    public function tarea()
    {
        return $this->belongsTo(Tarea::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Crear entrada en la bitácora
     */
    public static function registrar($tareaId, $accion, $descripcion, $usuarioId = null, $metadata = null)
    {
        return self::create([
            'tarea_id' => $tareaId,
            'usuario_id' => $usuarioId ?? auth()->id(),
            'accion' => $accion,
            'descripcion' => $descripcion,
            'metadata' => $metadata,
            'creado_en' => now(),
        ]);
    }
}
