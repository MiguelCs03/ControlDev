<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Tarea Asignada</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f4f6f9; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
    <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05); margin-top: 40px; margin-bottom: 40px;">
        
        <!-- Header -->
        <div style="background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%); padding: 30px; text-align: center;">
            <h1 style="color: #ffffff; margin: 0; font-size: 24px; font-weight: 600;">Nueva Tarea Asignada</h1>
            <p style="color: #e0e7ff; margin-top: 10px; font-size: 16px;">Se te ha asignado una nueva responsabilidad</p>
        </div>

        <!-- Content -->
        <div style="padding: 40px 30px;">
            <p style="color: #4b5563; font-size: 16px; line-height: 1.6; margin-bottom: 25px;">
                Hola <strong>{{ $tarea->responsable->name }}</strong>,
            </p>
            <p style="color: #4b5563; font-size: 16px; line-height: 1.6; margin-bottom: 30px;">
                Se te ha asignado la siguiente tarea en el proyecto <strong>{{ $tarea->proyecto->nombre }}</strong>. A continuación encontrarás los detalles principales:
            </p>

            <div style="background-color: #f9fafb; border-left: 4px solid #4f46e5; padding: 20px; border-radius: 4px; margin-bottom: 30px;">
                <h2 style="color: #1f2937; margin: 0 0 10px 0; font-size: 18px;">{{ $tarea->titulo }}</h2>
                
                @if($tarea->descripcion)
                <p style="color: #6b7280; font-size: 14px; margin-bottom: 15px; line-height: 1.5;">
                    {{ $tarea->descripcion }}
                </p>
                @endif

                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 8px 0; color: #6b7280; font-size: 14px; width: 100px;">Prioridad:</td>
                        <td style="padding: 8px 0;">
                            <span style="display: inline-block; padding: 4px 12px; border-radius: 99px; font-size: 12px; font-weight: 600; 
                                @if($tarea->prioridad === 'alta' || $tarea->prioridad === 'urgente') background-color: #fee2e2; color: #991b1b;
                                @elseif($tarea->prioridad === 'media') background-color: #fef3c7; color: #92400e;
                                @else background-color: #e5e7eb; color: #374151; @endif">
                                {{ ucfirst($tarea->prioridad) }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; color: #6b7280; font-size: 14px;">Estado:</td>
                        <td style="padding: 8px 0; color: #1f2937; font-size: 14px; font-weight: 500;">
                            {{ ucfirst(str_replace('_', ' ', $tarea->estado)) }}
                        </td>
                    </tr>
                    @if($tarea->fecha_inicio)
                    <tr>
                        <td style="padding: 8px 0; color: #6b7280; font-size: 14px;">Fecha Inicio:</td>
                        <td style="padding: 8px 0; color: #1f2937; font-size: 14px;">
                            {{ $tarea->fecha_inicio->format('d/m/Y') }}
                        </td>
                    </tr>
                    @endif
                    <tr>
                        <td style="padding: 8px 0; color: #6b7280; font-size: 14px;">Asignado por:</td>
                        <td style="padding: 8px 0; color: #1f2937; font-size: 14px;">
                            {{ $tarea->modificado_por ?? 'Sistema' }}
                        </td>
                    </tr>
                </table>
            </div>

            <div style="text-align: center; margin-top: 35px;">
                <a href="{{ config('app.url') }}" style="background-color: #4f46e5; color: #ffffff; text-decoration: none; padding: 12px 25px; border-radius: 6px; font-weight: 600; font-size: 16px; display: inline-block; transition: background-color 0.2s;">
                    Ver Tarea en el Sistema
                </a>
            </div>
        </div>

        <!-- Footer -->
        <div style="background-color: #f9fafb; padding: 20px; text-align: center; border-top: 1px solid #e5e7eb;">
            <p style="color: #9ca3af; font-size: 13px; margin: 0;">
                Este correo fue enviado automáticamente por el sistema de gestión de proyectos.
            </p>
            <p style="color: #9ca3af; font-size: 13px; margin: 5px 0 0 0;">
                &copy; {{ date('Y') }} {{ config('app.name') }}. Todos los derechos reservados.
            </p>
        </div>
    </div>
</body>
</html>
