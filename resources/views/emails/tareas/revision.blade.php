<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Tarea Lista para Revisión</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');
        
        body { 
            margin: 0; 
            padding: 0; 
            min-width: 100%; 
            background: linear-gradient(135deg, #0a0f1e 0%, #1a1f35 100%);
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; 
            color: #e2e8f0; 
        }
        
        table { border-spacing: 0; border-collapse: collapse; }
        td { padding: 0; }
        img { border: 0; }
        a { text-decoration: none; }
    </style>
</head>
<body style="background: linear-gradient(135deg, #0a0f1e 0%, #1a1f35 100%); margin: 0; padding: 40px 0;">
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
        <tr>
            <td align="center">
                <!-- Main Container -->
                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="600" style="max-width: 600px;">
                    
                    <!-- Space for logos/icons (to be added later) -->
                    <tr>
                        <td align="center" style="padding-bottom: 24px;">
                            <!-- Logo area - user will add Vue, Laravel, React, Python icons here -->
                        </td>
                    </tr>

                    <!-- Card Start with Premium Shadow -->
                    <tr>
                        <td style="background: linear-gradient(180deg, #1a1f35 0%, #111827 100%); border-radius: 16px; box-shadow: 0 20px 40px rgba(0, 0, 0, 0.5), 0 0 0 1px rgba(16, 185, 129, 0.1); overflow: hidden;">
                            
                            <!-- Gradient Header Bar -->
                            <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0">
                                <tr>
                                    <td style="background: linear-gradient(90deg, #10b981 0%, #059669 100%); height: 4px;"></td>
                                </tr>
                            </table>

                            <!-- Header -->
                            <tr>
                                <td style="padding: 48px 48px 32px 48px;">
                                    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                        <tr>
                                            <td align="center">
                                                <!-- Icon with Gradient Background -->
                                                <table role="presentation" border="0" cellspacing="0" cellpadding="0">
                                                    <tr>
                                                        <td style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); width: 64px; height: 64px; border-radius: 12px; text-align: center; box-shadow: 0 8px 16px rgba(16, 185, 129, 0.3);">
                                                            <svg width="36" height="36" viewBox="0 0 24 24" fill="none" style="margin-top: 14px;" xmlns="http://www.w3.org/2000/svg">
                                                                <path d="M9 12L11 14L15 10M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="#ffffff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                            </svg>
                                                        </td>
                                                    </tr>
                                                </table>

                                                <h1 style="color: #ffffff; margin: 24px 0 12px 0; font-size: 32px; font-weight: 800; letter-spacing: -0.8px; line-height: 1.2;">
                                                    Tarea Lista para Revisión
                                                </h1>
                                                <p style="color: #94a3b8; margin: 0; font-size: 16px; font-weight: 500;">
                                                    Sistema de Gestión de Proyectos
                                                </p>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>

                            <!-- Content -->
                            <tr>
                                <td style="padding: 0 48px 32px 48px;">
                                    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                        <!-- Greeting -->
                                        <tr>
                                            <td style="padding-bottom: 28px;">
                                                <p style="color: #e2e8f0; font-size: 18px; line-height: 1.6; margin: 0 0 12px 0; font-weight: 600;">
                                                    Estimado/a Administrador,
                                                </p>
                                                <p style="color: #94a3b8; font-size: 16px; line-height: 1.7; margin: 0;">
                                                    El desarrollador <strong style="color: #e2e8f0; font-weight: 700;">{{ $usuario->name }}</strong> ha completado el trabajo asignado y marcado la siguiente tarea como 
                                                    <span style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: #ffffff; padding: 3px 10px; border-radius: 6px; font-weight: 700; font-size: 14px; white-space: nowrap;">Lista para Revisión</span>. 
                                                    Se requiere su aprobación para continuar con el proceso.
                                                </p>
                                            </td>
                                        </tr>

                                        <!-- Task Card -->
                                        <tr>
                                            <td>
                                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background: rgba(15, 23, 42, 0.6); border: 1px solid rgba(16, 185, 129, 0.2); border-radius: 12px; margin-bottom: 28px; backdrop-filter: blur(10px); box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3);">
                                                    <tr>
                                                        <!-- Gradient Left Border -->
                                                        <td width="5" style="background: linear-gradient(180deg, #10b981 0%, #059669 100%); width: 5px; min-width: 5px; border-radius: 12px 0 0 12px;"></td>
                                                        
                                                        <td style="padding: 32px;">
                                                            <!-- Task Title -->
                                                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="margin-bottom: 20px;">
                                                                <tr>
                                                                    <td style="padding: 0 0 16px 0; border-bottom: 2px solid rgba(16, 185, 129, 0.2);">
                                                                        <h2 style="color: #ffffff; margin: 0; font-size: 24px; font-weight: 700; line-height: 1.3; letter-spacing: -0.4px;">
                                                                            {{ $tarea->titulo }}
                                                                        </h2>
                                                                        <p style="margin: 8px 0 0 0; font-size: 15px; color: #64748b; font-weight: 600;">
                                                                            ID: #{{ $tarea->id }}
                                                                        </p>
                                                                    </td>
                                                                </tr>
                                                            </table>
                                                            
                                                            <!-- Task Description -->
                                                            @if($tarea->descripcion)
                                                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="margin-bottom: 24px;">
                                                                <tr>
                                                                    <td style="background: rgba(16, 185, 129, 0.05); padding: 16px; border-radius: 8px; border-left: 3px solid #10b981;">
                                                                        <p style="color: #94a3b8; font-size: 16px; margin: 0; line-height: 1.7;">
                                                                            {{ $tarea->descripcion }}
                                                                        </p>
                                                                    </td>
                                                                </tr>
                                                            </table>
                                                            @endif

                                                            <!-- Task Details Grid -->
                                                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                                                <!-- Project -->
                                                                <tr>
                                                                    <td style="padding: 12px 0; color: #64748b; font-size: 15px; font-weight: 600; width: 180px; vertical-align: top;">
                                                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" style="vertical-align: middle; margin-right: 8px;" xmlns="http://www.w3.org/2000/svg">
                                                                            <path d="M3 7V17C3 18.1046 3.89543 19 5 19H19C20.1046 19 21 18.1046 21 17V7M3 7C3 5.89543 3.89543 5 5 5H19C20.1046 5 21 5.89543 21 7M3 7L12 13L21 7" stroke="#3b82f6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                                        </svg>
                                                                        Proyecto
                                                                    </td>
                                                                    <td style="padding: 12px 0; color: #e2e8f0; font-size: 16px; font-weight: 700; vertical-align: top;">
                                                                        {{ $tarea->proyecto->nombre }}
                                                                    </td>
                                                                </tr>

                                                                @if($tarea->proyecto->cliente)
                                                                <!-- Client -->
                                                                <tr>
                                                                    <td style="padding: 12px 0; color: #64748b; font-size: 13px; font-weight: 600;">
                                                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" style="vertical-align: middle; margin-right: 8px;" xmlns="http://www.w3.org/2000/svg">
                                                                            <path d="M19 21V19C19 17.9391 18.5786 16.9217 17.8284 16.1716C17.0783 15.4214 16.0609 15 15 15H9C7.93913 15 6.92172 15.4214 6.17157 16.1716C5.42143 16.9217 5 17.9391 5 19V21M16 7C16 9.20914 14.2091 11 12 11C9.79086 11 8 9.20914 8 7C8 4.79086 9.79086 3 12 3C14.2091 3 16 4.79086 16 7Z" stroke="#8b5cf6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                                        </svg>
                                                                        Cliente
                                                                    </td>
                                                                    <td style="padding: 12px 0; color: #e2e8f0; font-size: 14px; font-weight: 700;">
                                                                        {{ $tarea->proyecto->cliente->nombre }}
                                                                    </td>
                                                                </tr>
                                                                @endif

                                                                <!-- Developer -->
                                                                <tr>
                                                                    <td style="padding: 12px 0; color: #64748b; font-size: 13px; font-weight: 600;">
                                                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" style="vertical-align: middle; margin-right: 8px;" xmlns="http://www.w3.org/2000/svg">
                                                                            <path d="M8 3L4 7L8 11M16 3L20 7L16 11" stroke="#10b981" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                                            <path d="M12 21V3" stroke="#10b981" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                                        </svg>
                                                                        Desarrollador
                                                                    </td>
                                                                    <td style="padding: 12px 0; color: #e2e8f0; font-size: 14px; font-weight: 700;">
                                                                        {{ $usuario->name }}
                                                                    </td>
                                                                </tr>

                                                                <!-- Time Worked -->
                                                                <tr>
                                                                    <td style="padding: 12px 0; color: #64748b; font-size: 13px; font-weight: 600;">
                                                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" style="vertical-align: middle; margin-right: 8px;" xmlns="http://www.w3.org/2000/svg">
                                                                            <path d="M12 8V12L15 15M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="#f59e0b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                                        </svg>
                                                                        Tiempo Trabajado
                                                                    </td>
                                                                    <td style="padding: 12px 0; vertical-align: top;">
                                                                        <span style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: #ffffff; padding: 6px 16px; border-radius: 8px; font-weight: 700; font-size: 15px; display: inline-block; box-shadow: 0 4px 8px rgba(245, 158, 11, 0.3);">
                                                                            {{ $tarea->tiempo_total_trabajado ?? 0 }} horas
                                                                        </span>
                                                                    </td>
                                                                </tr>

                                                                @if($tarea->fecha_inicio)
                                                                <!-- Start Date -->
                                                                <tr>
                                                                    <td style="padding: 12px 0; color: #64748b; font-size: 13px; font-weight: 600;">
                                                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" style="vertical-align: middle; margin-right: 8px;" xmlns="http://www.w3.org/2000/svg">
                                                                            <path d="M8 7V3M16 7V3M7 11H17M5 21H19C20.1046 21 21 20.1046 21 19V7C21 5.89543 20.1046 5 19 5H5C3.89543 5 3 5.89543 3 7V19C3 20.1046 3.89543 21 5 21Z" stroke="#3b82f6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                                        </svg>
                                                                        Fecha de Inicio
                                                                    </td>
                                                                    <td style="padding: 12px 0; color: #94a3b8; font-size: 16px; font-weight: 600;">
                                                                        {{ $tarea->fecha_inicio->format('d/m/Y H:i') }}
                                                                    </td>
                                                                </tr>
                                                                @endif

                                                                <!-- Priority -->
                                                                <tr>
                                                                    <td style="padding: 12px 0; color: #64748b; font-size: 13px; font-weight: 600;">
                                                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" style="vertical-align: middle; margin-right: 8px;" xmlns="http://www.w3.org/2000/svg">
                                                                            <path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z" stroke="#fbbf24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                                        </svg>
                                                                        Prioridad
                                                                    </td>
                                                                    <td style="padding: 12px 0;">
                                                                        @php
                                                                            $priorityStyles = [
                                                                                'urgente' => 'background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);',
                                                                                'alta' => 'background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);',
                                                                                'media' => 'background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);',
                                                                                'baja' => 'background: linear-gradient(135deg, #64748b 0%, #475569 100%);'
                                                                            ];
                                                                            $priorityStyle = $priorityStyles[$tarea->prioridad] ?? $priorityStyles['baja'];
                                                                        @endphp
                                                                        <span style="display: inline-block; padding: 6px 16px; border-radius: 8px; font-size: 13px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; color: #ffffff; {{ $priorityStyle }} box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);">
                                                                            {{ ucfirst($tarea->prioridad) }}
                                                                        </span>
                                                                    </td>
                                                                </tr>
                                                            </table>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>

                                        <!-- CTA Button -->
                                        <tr>
                                            <td align="center" style="padding: 28px 0;">
                                                <table role="presentation" cellspacing="0" cellpadding="0" border="0">
                                                    <tr>
                                                        <td style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); border-radius: 10px; box-shadow: 0 10px 25px rgba(16, 185, 129, 0.4);">
                                                            <a href="{{ config('app.url') }}/ticketing-kanban?tarea={{ $tarea->id }}" 
                                                               style="color: #ffffff; text-decoration: none; padding: 18px 52px; border-radius: 10px; font-weight: 700; font-size: 17px; display: inline-block; letter-spacing: 0.3px;">
                                                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" style="vertical-align: middle; margin-right: 8px;" xmlns="http://www.w3.org/2000/svg">
                                                                    <path d="M9 12L11 14L15 10M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="#ffffff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                                </svg>
                                                                Revisar Tarea Ahora
                                                            </a>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>

                                        <!-- Info Box -->
                                        <tr>
                                            <td style="padding: 0;">
                                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background: rgba(245, 158, 11, 0.1); border-left: 4px solid #f59e0b; border-radius: 8px; padding: 20px;">
                                                    <tr>
                                                        <td>
                                                            <table role="presentation" border="0" cellspacing="0" cellpadding="0">
                                                                <tr>
                                                                    <td style="vertical-align: top; padding-right: 12px;">
                                                                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                            <path d="M12 9V13M12 17H12.01M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="#f59e0b" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                                        </svg>
                                                                    </td>
                                                                    <td>
                                                                        <p style="color: #fbbf24; font-size: 15px; margin: 0; line-height: 1.6; font-weight: 600;">
                                                                            <strong style="color: #fcd34d;">Acción Requerida:</strong> Se requiere su revisión y aprobación para finalizar esta tarea. El desarrollador está a la espera de su retroalimentación.
                                                                        </p>
                                                                    </td>
                                                                </tr>
                                                            </table>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>

                            <!-- Footer -->
                            <tr>
                                <td style="background: rgba(15, 23, 42, 0.4); padding: 32px 48px; border-top: 1px solid rgba(16, 185, 129, 0.2);">
                                    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                        <tr>
                                            <td align="center">
                                                <p style="color: #64748b; font-size: 14px; margin: 0 0 8px 0; line-height: 1.6; font-weight: 500;">
                                                    Este mensaje ha sido generado automáticamente por el sistema de gestión de proyectos.
                                                </p>
                                                <p style="color: #475569; font-size: 13px; margin: 0; font-weight: 500;">
                                                    &copy; {{ date('Y') }} {{ config('app.name') }}. Todos los derechos reservados.
                                                </p>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>

