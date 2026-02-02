<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Tarea extends Model
{
    use HasFactory;

    // Desactivar timestamps automáticos de Laravel
    public $timestamps = false;

    protected $fillable = [
        'titulo',
        'descripcion',
        'estado',
        'prioridad',
        'proyecto_id',
        'creador_id',
        'responsable_id',
        'fecha_inicio',
        'fecha_fin',
        'creado_por',
        'creado_en',
        'modificado_por',
        'modificado_en',
    ];

    protected $casts = [
        'fecha_inicio' => 'datetime',
        'fecha_fin' => 'datetime',
        'creado_en' => 'datetime',
        'modificado_en' => 'datetime',
    ];

    // Relaciones
    public function proyecto()
    {
        return $this->belongsTo(Proyecto::class);
    }

    public function creador()
    {
        return $this->belongsTo(User::class, 'creador_id');
    }

    public function responsable()
    {
        return $this->belongsTo(User::class, 'responsable_id');
    }

    public function registrosTiempos()
    {
        return $this->hasMany(RegistroTiempo::class);
    }

    public function comentarios()
    {
        return $this->hasMany(Comentario::class);
    }

    public function adjuntos()
    {
        return $this->hasMany(Adjunto::class);
    }

    public function bitacora()
    {
        return $this->hasMany(BitacoraTarea::class)->orderBy('creado_en', 'desc');
    }

    // Accessors
    public function getDiasTranscurridosAttribute()
    {
        if (!$this->fecha_inicio) {
            return 0;
        }

        $fechaFin = $this->fecha_fin ?? now();
        return $this->fecha_inicio->diffInDays($fechaFin);
    }

    public function getTiempoTotalTrabajadoAttribute()
    {
        // Sumar todos los tiempos de registros en minutos y convertir a horas
        $totalMinutos = $this->registrosTiempos()->sum('tiempo_transcurrido');
        return round($totalMinutos / 60, 2);
    }

    public function getDuracionTotalAttribute()
    {
        if (!$this->fecha_inicio || !$this->fecha_fin) {
            return null;
        }

        $diff = $this->fecha_inicio->diff($this->fecha_fin);
        return [
            'dias' => $diff->days,
            'horas' => $diff->h,
            'minutos' => $diff->i,
            'total_horas' => round($diff->days * 24 + $diff->h + ($diff->i / 60), 2)
        ];
    }

    // Boot method para registrar en bitácora automáticamente
    protected static function boot()
    {
        parent::boot();

        // Al crear: registrar creado_por y creado_en
        static::creating(function ($tarea) {
            if (Auth::check()) {
                $usuario = Auth::user();
                $tarea->creado_por = $usuario->name;
                $tarea->creado_en = now();
                $tarea->modificado_por = $usuario->name;
                $tarea->modificado_en = now();
                
                if (!$tarea->creador_id) {
                    $tarea->creador_id = Auth::id();
                }
            }
        });

        // Al actualizar: registrar modificado_por y modificado_en
        static::updating(function ($tarea) {
            if (Auth::check()) {
                $usuario = Auth::user();
                $tarea->modificado_por = $usuario->name;
                $tarea->modificado_en = now();
            }
        });

        // Después de crear: registrar en bitácora y notificar si hay responsable
        static::created(function ($tarea) {
            $nombreUsuario = $tarea->creado_por ?? 'Sistema';
            
            BitacoraTarea::registrar(
                $tarea->id,
                'creada',
                "{$nombreUsuario} creó la tarea \"{$tarea->titulo}\"",
                $tarea->creador_id
            );

            // Si se asignó responsable al crear
            if ($tarea->responsable_id) {
                $responsable = User::find($tarea->responsable_id);
                if ($responsable && $responsable->email) {
                    try {
                        \Illuminate\Support\Facades\Mail::to($responsable->email)
                            ->send(new \App\Mail\TareaAsignadaMailable($tarea));
                        
                        // También registrar asignación en bitácora para constancia
                        BitacoraTarea::registrar(
                            $tarea->id,
                            'asignada',
                            "Asignada automáticamente a {$responsable->name} en la creación"
                        );
                    } catch (\Exception $e) {
                         \Illuminate\Support\Facades\Log::error('Error enviando correo de asignación (creación): ' . $e->getMessage());
                    }
                }
            }
        });

        // Después de actualizar: detectar cambios y registrar en bitácora
        static::updated(function ($tarea) {
            $changes = $tarea->getChanges();
            $nombreUsuario = $tarea->modificado_por ?? 'Sistema';

            // Cambio de responsable (asignación)
            if (isset($changes['responsable_id']) && $changes['responsable_id']) {
                $responsable = User::find($changes['responsable_id']);
                BitacoraTarea::registrar(
                    $tarea->id,
                    'asignada',
                    "{$nombreUsuario} asignó la tarea \"{$tarea->titulo}\" a {$responsable->name}"
                );

                // Enviar correo de notificación
                if ($responsable && $responsable->email) {
                    try {
                        \Illuminate\Support\Facades\Mail::to($responsable->email)
                            ->send(new \App\Mail\TareaAsignadaMailable($tarea));
                    } catch (\Exception $e) {
                        // Log error but don't stop execution
                        \Illuminate\Support\Facades\Log::error('Error enviando correo de asignación de tarea: ' . $e->getMessage());
                    }
                }
            }

            // Cambio de estado
            if (isset($changes['estado'])) {
                $estadoAnterior = $tarea->getOriginal('estado');
                $estadoNuevo = $changes['estado'];

                $mensajes = [
                    'en_proceso' => "{$nombreUsuario} inició la tarea \"{$tarea->titulo}\"",
                    'en_revision' => "{$nombreUsuario} envió la tarea \"{$tarea->titulo}\" a revisión",
                    'finalizado' => "{$nombreUsuario} finalizó la tarea \"{$tarea->titulo}\"",
                    'cancelado' => "{$nombreUsuario} canceló la tarea \"{$tarea->titulo}\"",
                    'pendiente' => "{$nombreUsuario} movió la tarea \"{$tarea->titulo}\" a pendiente",
                ];

                $mensaje = $mensajes[$estadoNuevo] ?? "{$nombreUsuario} cambió el estado de \"{$tarea->titulo}\" a {$estadoNuevo}";

                BitacoraTarea::registrar(
                    $tarea->id,
                    $estadoNuevo,
                    $mensaje,
                    null,
                    ['estado_anterior' => $estadoAnterior, 'estado_nuevo' => $estadoNuevo]
                );

                // Si la tarea pasa a "en_revision", notificar a los administradores
                if ($estadoNuevo === 'en_revision') {
                    // Obtener administradores
                    // Asumiendo que el rol 1 es admin o buscando por nombre de rol
                    $admins = User::whereHas('roles', function ($query) {
                        $query->whereIn('nombre', ['admin', 'administrador', 'administrator']);
                    })->get();

                    foreach ($admins as $admin) {
                        if ($admin->email) {
                            try {
                                \Illuminate\Support\Facades\Mail::to($admin->email)
                                    ->send(new \App\Mail\TareaEnRevisionMailable($tarea, Auth::user()));
                            } catch (\Exception $e) {
                                \Illuminate\Support\Facades\Log::error('Error enviando correo de revisión a admin: ' . $e->getMessage());
                            }
                        }
                    }
                }
            }

            // Cambio de fecha_inicio
            if (isset($changes['fecha_inicio']) && $changes['fecha_inicio']) {
                BitacoraTarea::registrar(
                    $tarea->id,
                    'iniciada',
                    "{$nombreUsuario} estableció la fecha de inicio de la tarea \"{$tarea->titulo}\""
                );
            }

            // Cambio de fecha_fin
            if (isset($changes['fecha_fin']) && $changes['fecha_fin']) {
                $duracion = $tarea->duracion_total;
                $duracionTexto = $duracion ? "{$duracion['total_horas']} horas" : '';
                
                BitacoraTarea::registrar(
                    $tarea->id,
                    'finalizada',
                    "{$nombreUsuario} marcó como finalizada la tarea \"{$tarea->titulo}\" (Duración: {$duracionTexto})"
                );
            }

            // Cambio de prioridad
            if (isset($changes['prioridad'])) {
                $prioridadAnterior = $tarea->getOriginal('prioridad');
                $prioridadNueva = $changes['prioridad'];
                
                BitacoraTarea::registrar(
                    $tarea->id,
                    'modificada',
                    "{$nombreUsuario} cambió la prioridad de la tarea \"{$tarea->titulo}\" de {$prioridadAnterior} a {$prioridadNueva}"
                );
            }
        });
    }
}
