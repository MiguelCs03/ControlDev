<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class DiaFeriado extends Model
{
    protected $table = 'dias_feriados';

    protected $fillable = [
        'fecha',
        'nombre',
        'descripcion',
        'recurrente',
        'activo',
    ];

    protected $casts = [
        'fecha' => 'date',
        'recurrente' => 'boolean',
        'activo' => 'boolean',
    ];

    /**
     * Verificar si una fecha es feriado
     */
    public static function esFeriado($fecha)
    {
        $fecha = Carbon::parse($fecha);
        
        // Buscar feriado exacto
        $feriadoExacto = self::where('fecha', $fecha->format('Y-m-d'))
            ->where('activo', true)
            ->exists();
        
        if ($feriadoExacto) {
            return true;
        }
        
        // Buscar feriados recurrentes (mismo mes y día, cualquier año)
        $feriadoRecurrente = self::whereRaw('MONTH(fecha) = ? AND DAY(fecha) = ?', [
                $fecha->month,
                $fecha->day
            ])
            ->where('recurrente', true)
            ->where('activo', true)
            ->exists();
        
        return $feriadoRecurrente;
    }

    /**
     * Verificar si una fecha es día laborable
     * (no es fin de semana ni feriado)
     */
    public static function esDiaLaborable($fecha)
    {
        $fecha = Carbon::parse($fecha);
        
        // Verificar si es fin de semana (sábado = 6, domingo = 0)
        if ($fecha->dayOfWeek === Carbon::SATURDAY || $fecha->dayOfWeek === Carbon::SUNDAY) {
            return false;
        }
        
        // Verificar si es feriado
        if (self::esFeriado($fecha)) {
            return false;
        }
        
        return true;
    }

    /**
     * Obtener todos los días no laborables de un mes
     */
    public static function getDiasNoLaborables($mes, $anio)
    {
        $diasNoLaborables = [];
        $fecha = Carbon::create($anio, $mes, 1);
        $diasEnMes = $fecha->daysInMonth;
        
        for ($dia = 1; $dia <= $diasEnMes; $dia++) {
            $fechaActual = Carbon::create($anio, $mes, $dia);
            
            if (!self::esDiaLaborable($fechaActual)) {
                $tipo = 'fin_de_semana';
                $nombre = 'Fin de semana';
                
                if (self::esFeriado($fechaActual)) {
                    $tipo = 'feriado';
                    $feriado = self::where('fecha', $fechaActual->format('Y-m-d'))
                        ->where('activo', true)
                        ->first();
                    
                    if (!$feriado) {
                        // Es un feriado recurrente
                        $feriado = self::whereRaw('MONTH(fecha) = ? AND DAY(fecha) = ?', [
                                $fechaActual->month,
                                $fechaActual->day
                            ])
                            ->where('recurrente', true)
                            ->where('activo', true)
                            ->first();
                    }
                    
                    $nombre = $feriado ? $feriado->nombre : 'Feriado';
                }
                
                $diasNoLaborables[] = [
                    'fecha' => $fechaActual->format('Y-m-d'),
                    'tipo' => $tipo,
                    'nombre' => $nombre,
                ];
            }
        }
        
        return $diasNoLaborables;
    }

    /**
     * Calcular días laborables entre dos fechas
     */
    public static function calcularDiasLaborables($fechaInicio, $fechaFin)
    {
        $inicio = Carbon::parse($fechaInicio);
        $fin = Carbon::parse($fechaFin);
        $diasLaborables = 0;
        
        while ($inicio->lte($fin)) {
            if (self::esDiaLaborable($inicio)) {
                $diasLaborables++;
            }
            $inicio->addDay();
        }
        
        return $diasLaborables;
    }
}
