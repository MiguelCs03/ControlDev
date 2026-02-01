<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RegistroTiempo extends Model
{
    use HasFactory;

    protected $table = 'registros_tiempos';

    protected $fillable = [
        'tarea_id',
        'usuario_id',
        'fecha_inicio',
        'fecha_fin',
        'tiempo_transcurrido',
        'nota',
    ];

    protected $casts = [
        'fecha_inicio' => 'datetime',
        'fecha_fin' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
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

    // Boot method
    protected static function boot()
    {
        parent::boot();

        // Calcular tiempo transcurrido al guardar
        static::saving(function ($registro) {
            if ($registro->fecha_inicio && $registro->fecha_fin) {
                $registro->tiempo_transcurrido = $registro->fecha_inicio->diffInMinutes($registro->fecha_fin);
            }
        });
    }
}
