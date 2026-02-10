<?php

namespace App\Mail;

use App\Models\Tarea;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TareaCambioEstadoMailable extends Mailable
{
    use Queueable, SerializesModels;

    public $tarea;

    /**
     * Create a new message instance.
     */
    public function __construct(Tarea $tarea)
    {
        $this->tarea = $tarea;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $estadoTexto = [
            'pendiente' => 'Pendiente',
            'en_proceso' => 'En Proceso',
            'en_revision' => 'En Revisión',
            'finalizado' => 'Finalizado',
            'cancelado' => 'Cancelado'
        ];

        $estado = $estadoTexto[$this->tarea->estado] ?? 'Actualizado';

        return $this->subject("Tarea {$estado}: {$this->tarea->titulo}")
                    ->view('emails.tareas.cambio-estado');
    }
}
