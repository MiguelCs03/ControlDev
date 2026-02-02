<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tarea Lista para Revisión</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f4f6f9; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
    <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05); margin-top: 40px; margin-bottom: 40px;">
        
        <!-- Header -->
        <div style="background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%); padding: 30px; text-align: center;">
            <h1 style="color: #ffffff; margin: 0; font-size: 24px; font-weight: 600;">Tarea Lista para Revisión</h1>
            <p style="color: #ede9fe; margin-top: 10px; font-size: 16px;">Una tarea requiere tu atención</p>
        </div>

        <!-- Content -->
        <div style="padding: 40px 30px;">
            <p style="color: #4b5563; font-size: 16px; line-height: 1.6; margin-bottom: 25px;">
                Hola <strong>Admin</strong>,
            </p>
            <p style="color: #4b5563; font-size: 16px; line-height: 1.6; margin-bottom: 30px;">
                El usuario <strong>{{ $usuario->name }}</strong> ha marcado la siguiente tarea como terminada y lista para revisión:
            </p>

            <div style="background-color: #f9fafb; border-left: 4px solid #8b5cf6; padding: 20px; border-radius: 4px; margin-bottom: 30px;">
                <h2 style="color: #1f2937; margin: 0 0 10px 0; font-size: 18px;">{{ $tarea->titulo }}</h2>
                
                <div style="margin-bottom: 15px;">
                    <span style="font-size: 12px; color: #6b7280; text-transform: uppercase; letter-spacing: 0.5px;">Proyecto</span><br>
                    <span style="color: #1f2937; font-weight: 500;">{{ $tarea->proyecto->nombre }}</span>
                </div>

                @if($tarea->descripcion)
                <div style="margin-bottom: 15px;">
                    <span style="font-size: 12px; color: #6b7280; text-transform: uppercase; letter-spacing: 0.5px;">Descripción</span><br>
                    <span style="color: #4b5563; font-size: 14px; line-height: 1.5;">{{ $tarea->descripcion }}</span>
                </div>
                @endif
                
                <table style="width: 100%; border-collapse: collapse; margin-top: 10px;">
                    <tr>
                        <td style="padding: 5px 0; color: #6b7280; font-size: 13px; width: 120px;">Tiempo Trabajado:</td>
                        <td style="padding: 5px 0; color: #1f2937; font-size: 13px; font-weight: 600;">{{ $tarea->tiempo_total_trabajado }} horas</td>
                    </tr>
                    <tr>
                        <td style="padding: 5px 0; color: #6b7280; font-size: 13px;">Fecha Inicio:</td>
                        <td style="padding: 5px 0; color: #1f2937; font-size: 13px;">{{ $tarea->fecha_inicio ? $tarea->fecha_inicio->format('d/m/Y H:i') : '-' }}</td>
                    </tr>
                </table>
            </div>

            <div style="text-align: center; margin-top: 35px;">
                <a href="{{ config('app.url') }}" style="background-color: #8b5cf6; color: #ffffff; text-decoration: none; padding: 12px 25px; border-radius: 6px; font-weight: 600; font-size: 16px; display: inline-block; transition: background-color 0.2s;">
                    Revisar Tarea
                </a>
            </div>
        </div>

        <!-- Footer -->
        <div style="background-color: #f9fafb; padding: 20px; text-align: center; border-top: 1px solid #e5e7eb;">
            <p style="color: #9ca3af; font-size: 13px; margin: 0;">
                Este correo fue enviado automáticamente por el sistema de gestión de proyectos.
            </p>
        </div>
    </div>
</body>
</html>
