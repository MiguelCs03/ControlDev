<?php

namespace App\Exports;

use App\Models\Tarea;
use App\Models\DiaFeriado;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class TareasExport implements FromArray, WithHeadings, WithStyles, ShouldAutoSize
{
    protected $usuarioId;
    protected $proyectoId;
    protected $esAdmin;
    protected $fechaDesde;
    protected $fechaHasta;

    // Registro de qué filas son fin de semana, feriado, o día sin trabajo
    protected $filasNoLaborables  = []; // fila => 'weekend' | 'feriado'
    protected $filasVacias        = []; // fila => true  (laborable pero sin tareas)
    protected $totalFilas         = 0;

    public function __construct($usuarioId = null, $proyectoId = null, $esAdmin = false, $fechaDesde = null, $fechaHasta = null)
    {
        $this->usuarioId  = $usuarioId;
        $this->proyectoId = $proyectoId;
        $this->esAdmin    = $esAdmin;
        $this->fechaDesde = $fechaDesde;
        $this->fechaHasta = $fechaHasta;
    }

    // -------------------------------------------------------------------------
    // Datos
    // -------------------------------------------------------------------------
    public function array(): array
    {
        // 1. Cargar tareas
        $query = Tarea::with(['proyecto.cliente', 'responsable']);

        if ($this->usuarioId) {
            $query->where('responsable_id', $this->usuarioId);
        }
        if ($this->proyectoId) {
            $query->where('proyecto_id', $this->proyectoId);
        }

        // Aplicar filtro de rango de fechas en la query si se especificó
        if ($this->fechaDesde) {
            $query->whereDate('fecha_inicio', '>=', $this->fechaDesde);
        }
        if ($this->fechaHasta) {
            $query->whereDate('fecha_inicio', '<=', $this->fechaHasta);
        }

        $tareas = $query->orderBy('fecha_inicio')->get();

        // 2. Determinar rango de fechas
        if ($this->fechaDesde || $this->fechaHasta) {
            // Rango explícito indicado por el usuario
            $fechaMin = $this->fechaDesde
                ? Carbon::parse($this->fechaDesde)->startOfDay()
                : Carbon::parse($tareas->whereNotNull('fecha_inicio')->min('fecha_inicio') ?? now())->startOfDay();
            $fechaMax = $this->fechaHasta
                ? Carbon::parse($this->fechaHasta)->startOfDay()
                : Carbon::parse($tareas->whereNotNull('fecha_inicio')->max('fecha_inicio') ?? now())->startOfDay();
        } else {
            // Sin rango: derivar del mínimo y máximo de las tareas
            $fechaMin = $tareas->whereNotNull('fecha_inicio')->min('fecha_inicio');
            $fechaMax = $tareas->whereNotNull('fecha_inicio')->max('fecha_inicio');

            if (!$fechaMin) {
                $fechaMin = Carbon::now()->startOfMonth();
                $fechaMax = Carbon::now()->endOfMonth();
            } else {
                $fechaMin = Carbon::parse($fechaMin)->startOfDay();
                $fechaMax = Carbon::parse($fechaMax)->startOfDay();
            }
        }

        // 3. Cargar feriados en set para consulta O(1)
        $feriadosSet = [];
        DiaFeriado::where('activo', true)->get()->each(function ($f) use (&$feriadosSet) {
            $c = Carbon::parse($f->fecha);
            if ($f->recurrente) {
                $feriadosSet['r_' . $c->format('m-d')] = $f->nombre;
            } else {
                $feriadosSet[$c->format('Y-m-d')] = $f->nombre;
            }
        });

        $esFeriado = function (Carbon $c) use ($feriadosSet): ?string {
            if (isset($feriadosSet[$c->format('Y-m-d')])) {
                return $feriadosSet[$c->format('Y-m-d')];
            }
            if (isset($feriadosSet['r_' . $c->format('m-d')])) {
                return $feriadosSet['r_' . $c->format('m-d')];
            }
            return null;
        };

        // 4. Indexar tareas por fecha_inicio (Y-m-d)
        $tareasPorDia = [];
        foreach ($tareas as $t) {
            if (!$t->fecha_inicio) continue;
            $key = Carbon::parse($t->fecha_inicio)->format('Y-m-d');
            $tareasPorDia[$key][] = $t;
        }

        // 5. Generar filas — una por cada día del rango
        $rows   = [];
        $rowNum = 2; // fila 1 = encabezado
        $dia    = $fechaMin->copy();

        while ($dia->lte($fechaMax)) {
            $key           = $dia->format('Y-m-d');
            $nombreFeriado = $esFeriado($dia);
            $esFinSemana   = $dia->isSaturday() || $dia->isSunday();
            $tareasDia     = $tareasPorDia[$key] ?? [];

            $nombreDia     = ucfirst($dia->locale('es')->isoFormat('dddd'));
            $etiquetaDia   = $esFinSemana
                ? "($nombreDia — Fin de semana)"
                : ($nombreFeriado ? "($nombreDia — Feriado: $nombreFeriado)" : "($nombreDia)");

            if (empty($tareasDia)) {
                // Día sin trabajo — fila vacía
                $motivo = $esFinSemana ? 'weekend' : ($nombreFeriado ? 'feriado' : null);

                $rows[] = [
                    $dia->format('d/m/Y') . ' ' . $etiquetaDia,
                    '-',   // Desarrollador
                    '-',   // Proyecto
                    '-',   // Módulo
                    '-',   // Vista
                    '-',   // Cosas Realizadas
                    '-',   // Estado
                    '-',   // Nota
                ];

                if ($motivo) {
                    $this->filasNoLaborables[$rowNum] = $motivo;
                } else {
                    $this->filasVacias[$rowNum] = true;
                }
                $rowNum++;
            } else {
                // Una fila por cada tarea del día
                foreach ($tareasDia as $tarea) {
                    $rows[] = [
                        $dia->format('d/m/Y') . ' ' . $etiquetaDia,
                        $tarea->responsable->name ?? 'Sin asignar',
                        $tarea->proyecto->nombre ?? '-',
                        $tarea->modulo ?? '-',
                        $tarea->vista ?? '-',
                        $tarea->descripcion ?? '-',
                        ucfirst(str_replace('_', ' ', $tarea->estado)),
                        $tarea->nota ?? '-',
                    ];

                    if ($esFinSemana) {
                        $this->filasNoLaborables[$rowNum] = 'weekend';
                    } elseif ($nombreFeriado) {
                        $this->filasNoLaborables[$rowNum] = 'feriado';
                    }
                    $rowNum++;
                }
            }

            $dia->addDay();
        }

        $this->totalFilas = $rowNum - 1;
        return $rows;
    }

    // -------------------------------------------------------------------------
    // Encabezados
    // -------------------------------------------------------------------------
    public function headings(): array
    {
        return [
            'Fecha',
            'Desarrollador',
            'Proyecto',
            'Módulo',
            'Vista',
            'Cosas Realizadas',
            'Estado',
            'Nota',
        ];
    }

    // -------------------------------------------------------------------------
    // Estilos
    // -------------------------------------------------------------------------
    public function styles(Worksheet $sheet)
    {
        $lastCol = 'H';

        // --- Encabezado ---
        $sheet->getStyle("A1:{$lastCol}1")->applyFromArray([
            'font' => [
                'bold'  => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size'  => 11,
            ],
            'fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1743C8'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Altura fila encabezado
        $sheet->getRowDimension(1)->setRowHeight(22);

        // --- Filas no laborables (fin de semana / feriado) → ROJO ---
        foreach ($this->filasNoLaborables as $fila => $tipo) {
            $sheet->getStyle("A{$fila}:{$lastCol}{$fila}")->applyFromArray([
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'FFCDD2'],
                ],
                'font' => [
                    'color'  => ['rgb' => 'B71C1C'],
                    'italic' => true,
                ],
            ]);
        }

        // --- Filas laborables sin trabajo → AMARILLO SUAVE ---
        foreach ($this->filasVacias as $fila => $_) {
            $sheet->getStyle("A{$fila}:{$lastCol}{$fila}")->applyFromArray([
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'FFF9C4'],
                ],
                'font' => [
                    'color' => ['rgb' => '795548'],
                ],
            ]);
        }

        // --- Bordes sutiles en toda la tabla ---
        if ($this->totalFilas >= 1) {
            $sheet->getStyle("A1:{$lastCol}{$this->totalFilas}")->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color'       => ['rgb' => 'E0E0E0'],
                    ],
                ],
            ]);
        }

        // --- Ancho fijo para columna Fecha (más ancha) ---
        $sheet->getColumnDimension('A')->setWidth(32);
        $sheet->getColumnDimension('F')->setWidth(45); // Cosas Realizadas
    }
}


