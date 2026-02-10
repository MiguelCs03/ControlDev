<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Tarea Asignada</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700\u0026display=swap');
        
        body { 
            margin: 0; 
            padding: 0; 
            min-width: 100%; 
            background-color: #0f172a;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; 
            color: #e2e8f0;
        }

        .container {
            max-width: 600px;
            width: 100%;
            margin: 0 auto;
        }
        
        @media only screen and (max-width: 600px) {
            .container { width: 100% !important; max-width: 100% !important; }
            .content-padding { padding: 20px !important; }
            .header-title { font-size: 24px !important; }
            .task-title { font-size: 20px !important; }
            .grid-item { display: block !important; width: 100% !important; padding-bottom: 12px !important; }
        }
    </style>
</head>
<body style="background-color: #0f172a; margin: 0; padding: 0;">

    <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0">
        <tr>
            <td align="center" style="padding: 20px 0;">
                
                <!-- Main Container -->
                <table role="presentation" class="container" border="0" cellspacing="0" cellpadding="0">
                    
                    <!-- Header with accent bar -->
                    <tr>
                        <td style="background: #1e293b; border-radius: 12px 12px 0 0; overflow: hidden; border-bottom: 1px solid #334155;">
                            <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0">
                                <tr>
                                    <td style="background: linear-gradient(90deg, #3b82f6 0%, #8b5cf6 100%); height: 4px;"></td>
                                </tr>
                                <tr>
                                    <td class="content-padding" style="padding: 32px 40px;">
                                        <h1 class="header-title" style="margin: 0 0 12px 0; font-size: 28px; font-weight: 700; color: #ffffff; letter-spacing: -0.5px;">
                                            Nueva Tarea Asignada
                                        </h1>
                                        <p style="margin: 0; font-size: 16px; color: #94a3b8; line-height: 1.5;">
                                            Hola <strong style="color: #e2e8f0;">{{ $tarea->responsable->name }}</strong>, tienes una nueva asignación.
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Task Content -->
                    <tr>
                        <td style="background: #1e293b; border-radius: 0 0 12px 12px; border: 1px solid #334155; border-top: none;">
                            <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0">
                                <tr>
                                    <td class="content-padding" style="padding: 0 40px 40px 40px;">
                                        
                                        <!-- Priority Badge & ID -->
                                        <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0" style="margin-bottom: 20px;">
                                            <tr>
                                                <td>
                                                    @php
                                                        $priorityColors = [
                                                            'urgente' => ['bg' => 'rgba(239, 68, 68, 0.2)', 'text' => '#fca5a5', 'border' => '#ef4444'],
                                                            'alta' => ['bg' => 'rgba(245, 158, 11, 0.2)', 'text' => '#fcd34d', 'border' => '#f59e0b'],
                                                            'media' => ['bg' => 'rgba(59, 130, 246, 0.2)', 'text' => '#93c5fd', 'border' => '#3b82f6'],
                                                            'baja' => ['bg' => 'rgba(100, 116, 139, 0.2)', 'text' => '#cbd5e1', 'border' => '#64748b']
                                                        ];
                                                        $p = $priorityColors[$tarea->prioridad] ?? $priorityColors['baja'];
                                                    @endphp
                                                    <span style="display: inline-block; font-size: 12px; font-weight: 700; color: {{ $p['text'] }}; text-transform: uppercase; letter-spacing: 0.5px; background: {{ $p['bg'] }}; padding: 4px 10px; border-radius: 4px; border: 1px solid {{ $p['border'] }};">
                                                        Prioridad {{ ucfirst($tarea->prioridad) }}
                                                    </span>
                                                    <span style="color: #64748b; font-size: 14px; margin-left: 10px; font-weight: 500;">
                                                        #{{ $tarea->id }}
                                                    </span>
                                                </td>
                                            </tr>
                                        </table>

                                        <!-- ID and Title -->
                                        <h2 class="task-title" style="margin: 0 0 16px 0; font-size: 24px; font-weight: 600; color: #ffffff; line-height: 1.3;">
                                            {{ $tarea->titulo }}
                                        </h2>

                                        <!-- Project Info Block -->
                                        <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0" style="background: #0f172a; border-radius: 8px; border: 1px solid #334155; margin-bottom: 24px;">
                                            <tr>
                                                <td style="padding: 20px;">
                                                    <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0">
                                                        <tr>
                                                            <td class="grid-item" width="50%" valign="top" style="padding-bottom: 16px;">
                                                                <p style="margin: 0 0 4px 0; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; color: #64748b; font-weight: 600;">Proyecto</p>
                                                                <p style="margin: 0; font-size: 15px; color: #e2e8f0; font-weight: 500;">{{ $tarea->proyecto->nombre }}</p>
                                                            </td>
                                                            <td class="grid-item" width="50%" valign="top" style="padding-bottom: 16px;">
                                                                <p style="margin: 0 0 4px 0; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; color: #64748b; font-weight: 600;">Cliente</p>
                                                                <p style="margin: 0; font-size: 15px; color: #e2e8f0; font-weight: 500;">{{ $tarea->proyecto->cliente->nombre ?? 'N/A' }}</p>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            @if($tarea->modulo)
                                                            <td class="grid-item" width="50%" valign="top">
                                                                <p style="margin: 0 0 4px 0; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; color: #64748b; font-weight: 600;">Módulo</p>
                                                                <p style="margin: 0; font-size: 15px; color: #e2e8f0; font-weight: 500;">{{ $tarea->modulo }}</p>
                                                            </td>
                                                            @endif
                                                            
                                                            @if($tarea->vista)
                                                            <td class="grid-item" width="50%" valign="top">
                                                                <p style="margin: 0 0 4px 0; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; color: #64748b; font-weight: 600;">Vista</p>
                                                                <p style="margin: 0; font-size: 15px; color: #e2e8f0; font-weight: 500;">{{ $tarea->vista }}</p>
                                                            </td>
                                                            @endif
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                        </table>

                                        <!-- Description -->
                                        @if($tarea->descripcion)
                                        <div style="margin-bottom: 24px;">
                                            <p style="margin: 0 0 8px 0; font-size: 13px; color: #94a3b8; font-weight: 600;">DESCRIPCIÓN</p>
                                            <p style="margin: 0; font-size: 15px; color: #cbd5e1; line-height: 1.6;">
                                                {{ $tarea->descripcion }}
                                            </p>
                                        </div>
                                        @endif

                                        <!-- Nota (si existe) -->
                                        @if($tarea->nota)
                                        <div style="margin-bottom: 24px; background: rgba(59, 130, 246, 0.1); border-left: 3px solid #3b82f6; padding: 12px 16px; border-radius: 0 4px 4px 0;">
                                            <p style="margin: 0 0 4px 0; font-size: 12px; color: #60a5fa; font-weight: 700; text-transform: uppercase;">Nota Adicional</p>
                                            <p style="margin: 0; font-size: 14px; color: #e2e8f0; font-style: italic;">
                                                "{{ $tarea->nota }}"
                                            </p>
                                        </div>
                                        @endif

                                        <!-- CTA Button -->
                                        <table role="presentation" border="0" cellspacing="0" cellpadding="0" style="margin-top: 32px;">
                                            <tr>
                                                <td style="background: #3b82f6; border-radius: 6px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);">
                                                    <a href="{{ config('app.url') }}/ticketing-kanban?tarea={{ $tarea->id }}" target="_blank" style="color: #ffffff; padding: 14px 32px; text-decoration: none; font-weight: 600; font-size: 16px; display: inline-block;">
                                                        Ver Tarea Completa &rarr;
                                                    </a>
                                                </td>
                                            </tr>
                                        </table>

                                        <!-- Footer Info -->
                                        <p style="margin: 32px 0 0 0; font-size: 13px; color: #64748b;">
                                            Asignado por <span style="color: #94a3b8;">{{ $tarea->creado_por ?? 'Sistema' }}</span> • {{ now()->format('d/m/Y H:i') }}
                                        </p>

                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    
                    <!-- Footer Links -->
                    <tr>
                        <td align="center" style="padding-top: 24px;">
                            <p style="margin: 0; font-size: 12px; color: #475569;">
                                &copy; {{ date('Y') }} {{ config('app.name') }}. Sistema de Gestión de Tareas.
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>

</body>
</html>