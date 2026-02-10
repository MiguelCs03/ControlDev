<?php

namespace App\Exports;

use App\Models\Tarea;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use App\Models\DiaFeriado;

class TareasExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $usuarioId;
    protected $proyectoId;
    protected $esAdmin;

    public function __construct($usuarioId = null, $proyectoId = null, $esAdmin = false)
    {
        $this->usuarioId = $usuarioId;
        $this->proyectoId = $proyectoId;
        $this->esAdmin = $esAdmin;
    }

    /**
     * Obtener la colección de tareas a exportar
     */
    public function collection()
    {
        $query = Tarea::with(['proyecto.cliente', 'responsable', 'registrosTiempos']);

        // Si es admin y no especifica usuario, trae todas
        if (!$this->esAdmin && $this->usuarioId) {
            // Usuario normal: solo sus tareas
            $query->where('responsable_id', $this->usuarioId);
        } elseif ($this->usuarioId) {
            // Admin filtrando por usuario específico
            $query->where('responsable_id', $this->usuarioId);
        }

        // Filtrar por proyecto si se especifica
        if ($this->proyectoId) {
            $query->where('proyecto_id', $this->proyectoId);
        }

        return $query->orderBy('creado_en', 'desc')->get();
    }

    /**
     * Encabezados de las columnas
     */
    public function headings(): array
    {
        $headings = [
            'ID',
            'Tarea',
            'Proyecto',
            'Cliente',
            'Descripción',
            'Estado',
            'Prioridad',
            'Responsable',
            'Fecha Inicio',
            'Fecha Fin',
            'Duración (días)',
            'Días Laborables',
            'Días No Laborables',
            'Tiempo Trabajado (hrs)',
            'Observaciones',
            'Creado Por',
            'Creado En',
        ];

        return $headings;
    }


    /**
     * Mapear cada tarea a sus columnas
     */
    public function map($tarea): array
    {
        // Calcular tiempo trabajado
        $tiempoTrabajado = $tarea->tiempo_total_trabajado ?? 0;

        // Calcular duración en días
        $duracion = '-';
        $diasLaborables = '-';
        $diasNoLaborables = '-';
        
        if ($tarea->fecha_inicio && $tarea->fecha_fin) {
            $totalDias = $tarea->fecha_inicio->diffInDays($tarea->fecha_fin) + 1;
            $duracion = $totalDias;
            
            // Calcular días laborables
            $laborables = DiaFeriado::calcularDiasLaborables(
                $tarea->fecha_inicio,
                $tarea->fecha_fin
            );
            $diasLaborables = $laborables;
            $diasNoLaborables = $totalDias - $laborables;
        } elseif ($tarea->fecha_inicio) {
            $totalDias = $tarea->fecha_inicio->diffInDays(now()) + 1;
            $duracion = $totalDias . ' (en curso)';
            
            // Calcular días laborables hasta hoy
            $laborables = DiaFeriado::calcularDiasLaborables(
                $tarea->fecha_inicio,
                now()
            );
            $diasLaborables = $laborables . ' (en curso)';
            $diasNoLaborables = $totalDias - $laborables;
        }

        // Observaciones (últimos comentarios o bitácora)
        $observaciones = '';
        if ($tarea->comentarios && $tarea->comentarios->count() > 0) {
            $ultimoComentario = $tarea->comentarios->last();
            $observaciones = substr($ultimoComentario->contenido, 0, 100);
        }

        return [
            $tarea->id,
            $tarea->titulo,
            $tarea->proyecto->nombre ?? '-',
            $tarea->proyecto->cliente->nombre ?? '-',
            substr($tarea->descripcion ?? '-', 0, 150),
            ucfirst(str_replace('_', ' ', $tarea->estado)),
            ucfirst($tarea->prioridad),
            $tarea->responsable->name ?? 'Sin asignar',
            $tarea->fecha_inicio ? $tarea->fecha_inicio->format('d/m/Y H:i') : '-',
            $tarea->fecha_fin ? $tarea->fecha_fin->format('d/m/Y H:i') : '-',
            $duracion,
            $diasLaborables,
            $diasNoLaborables,
            number_format($tiempoTrabajado, 2),
            $observaciones,
            $tarea->creado_por ?? '-',
            $tarea->creado_en ? $tarea->creado_en->format('d/m/Y H:i') : '-',
        ];
    }


    /**
     * Estilos para el Excel
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Estilo para el encabezado
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4CAF50'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }
}
