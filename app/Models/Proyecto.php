<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Proyecto extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'descripcion',
        'estado',
        'cliente_id',
        'fecha_inicio',
        'fecha_fin_estimada',
        'fecha_fin_real',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin_estimada' => 'date',
        'fecha_fin_real' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relaciones
    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function tareas()
    {
        return $this->hasMany(Tarea::class);
    }

    // Accesor para obtener el progreso del proyecto
    public function getProgresoAttribute()
    {
        $total = $this->tareas()->count();
        if ($total === 0) return 0;
        
        $finalizadas = $this->tareas()->where('estado', 'finalizado')->count();
        return round(($finalizadas / $total) * 100, 2);
    }

    // Accesor para obtener tareas pendientes
    public function getTareasPendientesAttribute()
    {
        return $this->tareas()->whereIn('estado', ['pendiente', 'en_proceso', 'en_revision'])->count();
    }
}
