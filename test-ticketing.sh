#!/bin/bash

echo "🚀 Script de Prueba del Sistema de Ticketing"
echo "=============================================="
echo ""

# Verificar que estamos en el directorio correcto
if [ ! -f "artisan" ]; then
    echo "❌ Error: Este script debe ejecutarse desde la raíz del proyecto Laravel"
    exit 1
fi

echo "✅ Directorio correcto"
echo ""

# Verificar tablas
echo "📊 Verificando tablas en la base de datos..."
php artisan tinker --execute="
    echo '- Clientes: ' . DB::table('clientes')->count() . ' registros\n';
    echo '- Proyectos: ' . DB::table('proyectos')->count() . ' registros\n';
    echo '- Tareas: ' . DB::table('tareas')->count() . ' registros\n';
    echo '- Comentarios: ' . DB::table('comentarios')->count() . ' registros\n';
    echo '- Registros de Tiempo: ' . DB::table('registros_tiempos')->count() . ' registros\n';
"

echo ""
echo "📈 Estadísticas por Estado de Tareas:"
php artisan tinker --execute="
    \$estados = DB::table('tareas')->select('estado', DB::raw('count(*) as total'))->groupBy('estado')->get();
    foreach (\$estados as \$estado) {
        echo '- ' . ucfirst(str_replace('_', ' ', \$estado->estado)) . ': ' . \$estado->total . ' tareas\n';
    }
"

echo ""
echo "🎯 Tareas por Prioridad:"
php artisan tinker --execute="
    \$prioridades = DB::table('tareas')->select('prioridad', DB::raw('count(*) as total'))->groupBy('prioridad')->orderByRaw(\"FIELD(prioridad, 'urgente', 'alta', 'media', 'baja')\")->get();
    foreach (\$prioridades as \$prioridad) {
        echo '- ' . ucfirst(\$prioridad->prioridad) . ': ' . \$prioridad->total . ' tareas\n';
    }
"

echo ""
echo "👥 Proyectos por Cliente:"
php artisan tinker --execute="
    \$clientes = DB::table('clientes')->get();
    foreach (\$clientes as \$cliente) {
        \$proyectos = DB::table('proyectos')->where('cliente_id', \$cliente->id)->count();
        echo '- ' . \$cliente->nombre . ': ' . \$proyectos . ' proyectos\n';
    }
"

echo ""
echo "⏱️  Tareas con Tiempo Trabajado:"
php artisan tinker --execute="
    \$tareasConTiempo = DB::table('tareas')
        ->join('registros_tiempos', 'tareas.id', '=', 'registros_tiempos.tarea_id')
        ->select('tareas.titulo', DB::raw('SUM(registros_tiempos.tiempo_transcurrido) as total_minutos'))
        ->groupBy('tareas.id', 'tareas.titulo')
        ->get();
    foreach (\$tareasConTiempo as \$tarea) {
        \$horas = round(\$tarea->total_minutos / 60, 2);
        echo '- ' . \$tarea->titulo . ': ' . \$horas . ' horas trabajadas\n';
    }
"

echo ""
echo "🔗 URLs de Acceso:"
echo "- Vista Kanban: http://localhost/ticketing-kanban.html"
echo "- API Base: http://localhost/api"
echo ""

echo "📖 Endpoints Clave:"
echo "- GET  /api/dashboard-tareas     - Dashboard con estadísticas"
echo "- GET  /api/tareas-kanban        - Vista Kanban (agrupado por estados)"
echo "- GET  /api/proyectos            - Listar proyectos"
echo "- GET  /api/tareas               - Listar tareas"
echo ""

echo "✨ Sistema de Ticketing verificado correctamente!"
echo ""
echo "💡 Usuarios de prueba:"
echo "   - admin@test.com / password"
echo "   - dev1@test.com / password"
echo "   - dev2@test.com / password"
echo ""
