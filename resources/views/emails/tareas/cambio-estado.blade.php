<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cambio de Estado de Tarea</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700\u0026display=swap');
        
        body { 
            margin: 0; 
            padding: 0; 
            min-width: 100%; 
            background-color: #f8fafc;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; 
        }
        
        @media only screen and (max-width: 600px) {
            .container { width: 100% !important; }
            .mobile-padding { padding: 24px !important; }
            .mobile-text { font-size: 14px !important; }
            .mobile-title { font-size: 20px !important; }
        }
    </style>
</head>
<body style="background-color: #f8fafc; margin: 0; padding: 20px 0;">

    <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0">
        <tr>
            <td align="center">
                
                <!-- Main Container -->
                <table role="presentation" class="container" width="600" border="0" cellspacing="0" cellpadding="0" style="max-width: 600px; width: 100%;">
                    
                    <!-- Header Logo/Brand -->
                    <tr>
                        <td align="center" style="padding-bottom: 24px;">
                            <h3 style="margin: 0; font-size: 18px; font-weight: 600; color: #1e293b;">
                                {{ config('app.name') }}
                            </h3>
                        </td>
                    </tr>

                    <!-- Card Start -->
                    <tr>
                        <td style="background: #ffffff; border-radius: 12px; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1); overflow: hidden; border: 1px solid #e2e8f0;">
                            
                            <!-- Status Color Bar -->
                            @php
                                $statusConfig = [
                                    'pendiente' => ['color' => '#f59e0b', 'bg' => '#fef3c7', 'text' => 'Pendiente', 'icon' => 'clock'],
                                    'en_proceso' => ['color' => '#3b82f6', 'bg' => '#dbeafe', 'text' => 'En Proceso', 'icon' => 'play'],
                                    'en_revision' => ['color' => '#8b5cf6', 'bg' => '#ede9fe', 'text' => 'En Revisión', 'icon' => 'eye'],
                                    'finalizado' => ['color' => '#10b981', 'bg' => '#d1fae5', 'text' => 'Finalizado', 'icon' => 'check'],
                                    'cancelado' => ['color' => '#ef4444', 'bg' => '#fee2e2', 'text' => 'Cancelado', 'icon' => 'x']
                                ];
                                
                                $status = $statusConfig[$tarea->estado] ?? $statusConfig['pendiente'];
                            @endphp

                            <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0">
                                <tr>
                                    <td style="background: {{ $status['color'] }}; height: 4px;"></td>
                                </tr>
                            </table>

                            <!-- Content -->
                            <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0">
                                <tr>
                                    <td class="mobile-padding" style="padding: 40px;">
                                        
                                        <!-- Status Badge -->
                                        <table role="presentation" border="0" cellspacing="0" cellpadding="0" style="margin-bottom: 24px;">
                                            <tr>
                                                <td style="background: {{ $status['bg'] }}; padding: 8px 16px; border-radius: 20px; display: inline-block;">
                                                    <span style="color: {{ $status['color'] }}; font-size: 14px; font-weight: 600; letter-spacing: 0.3px;">
                                                        {{ $status['text'] }}
                                                    </span>
                                                </td>
                                            </tr>
                                        </table>

                                        <!-- Title -->
                                        <h1 class="mobile-title" style="margin: 0 0 12px 0; font-size: 24px; font-weight: 700; color: #0f172a; line-height: 1.3;">
                                            {{ $tarea->titulo }}
                                        </h1>

                                        <!-- Subtitle -->
                                        <p style="margin: 0 0 32px 0; font-size: 16px; color: #64748b; line-height: 1.5;">
                                            El estado de esta tarea ha cambiado
                                        </p>

                                        <!-- Task Details Card -->
                                        <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0" style="background: #f8fafc; border-radius: 8px; border: 1px solid #e2e8f0; margin-bottom: 24px;">
                                            <tr>
                                                <td style="padding: 24px;">
                                                    
                                                    <!-- Project -->
                                                    <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0" style="margin-bottom: 16px;">
                                                        <tr>
                                                            <td width="120" style="color: #64748b; font-size: 14px; font-weight: 500;">
                                                                Proyecto
                                                            </td>
                                                            <td style="color: #0f172a; font-size: 14px; font-weight: 600;">
                                                                {{ $tarea->proyecto->nombre }}
                                                            </td>
                                                        </tr>
                                                    </table>

                                                    <!-- Client -->
                                                    @if($tarea->proyecto->cliente)
                                                    <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0" style="margin-bottom: 16px;">
                                                        <tr>
                                                            <td width="120" style="color: #64748b; font-size: 14px; font-weight: 500;">
                                                                Cliente
                                                            </td>
                                                            <td style="color: #0f172a; font-size: 14px; font-weight: 600;">
                                                                {{ $tarea->proyecto->cliente->nombre }}
                                                            </td>
                                                        </tr>
                                                    </table>
                                                    @endif

                                                    <!-- Responsible -->
                                                    @if($tarea->responsable)
                                                    <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0" style="margin-bottom: 16px;">
                                                        <tr>
                                                            <td width="120" style="color: #64748b; font-size: 14px; font-weight: 500;">
                                                                Responsable
                                                            </td>
                                                            <td style="color: #0f172a; font-size: 14px; font-weight: 600;">
                                                                {{ $tarea->responsable->name }}
                                                            </td>
                                                        </tr>
                                                    </table>
                                                    @endif

                                                    <!-- Priority -->
                                                    <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0">
                                                        <tr>
                                                            <td width="120" style="color: #64748b; font-size: 14px; font-weight: 500;">
                                                                Prioridad
                                                            </td>
                                                            <td>
                                                                @php
                                                                    $priorityColors = [
                                                                        'urgente' => '#ef4444',
                                                                        'alta' => '#f59e0b',
                                                                        'media' => '#3b82f6',
                                                                        'baja' => '#64748b'
                                                                    ];
                                                                    $priorityColor = $priorityColors[$tarea->prioridad] ?? '#64748b';
                                                                @endphp
                                                                <span style="color: {{ $priorityColor }}; font-size: 14px; font-weight: 600; text-transform: capitalize;">
                                                                    {{ $tarea->prioridad }}
                                                                </span>
                                                            </td>
                                                        </tr>
                                                    </table>

                                                </td>
                                            </tr>
                                        </table>

                                        <!-- Description -->
                                        @if($tarea->descripcion)
                                        <p class="mobile-text" style="margin: 0 0 32px 0; font-size: 15px; color: #475569; line-height: 1.6; padding: 16px; background: #f8fafc; border-left: 3px solid {{ $status['color'] }}; border-radius: 4px;">
                                            {{ \Illuminate\Support\Str::limit($tarea->descripcion, 200) }}
                                        </p>
                                        @endif

                                        <!-- CTA Button -->
                                        <table role="presentation" border="0" cellspacing="0" cellpadding="0" style="margin-bottom: 24px;">
                                            <tr>
                                                <td style="background: #0f172a; border-radius: 8px;">
                                                    <a href="{{ config('app.url') }}/ticketing-kanban?tarea={{ $tarea->id }}" target="_blank" style="color: #ffffff; padding: 14px 32px; text-decoration: none; font-weight: 600; font-size: 15px; display: inline-block;">
                                                        Ver Tarea Completa
                                                    </a>
                                                </td>
                                            </tr>
                                        </table>

                                        <!-- Modified By -->
                                        <p style="margin: 0; font-size: 13px; color: #94a3b8;">
                                            Actualizado por <strong style="color: #64748b;">{{ $tarea->modificado_por ?? 'Sistema' }}</strong>
                                            @if($tarea->modificado_en)
                                            el {{ $tarea->modificado_en->format('d/m/Y H:i') }}
                                            @endif
                                        </p>

                                    </td>
                                </tr>
                            </table>

                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td align="center" style="padding-top: 32px;">
                            <p style="margin: 0 0 8px 0; font-size: 13px; color: #94a3b8;">
                                Este es un correo automático, por favor no responder.
                            </p>
                            <p style="margin: 0; font-size: 12px; color: #cbd5e1;">
                                &copy; {{ date('Y') }} {{ config('app.name') }}. Todos los derechos reservados.
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>

</body>
</html>
