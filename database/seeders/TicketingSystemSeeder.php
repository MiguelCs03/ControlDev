<?php

namespace Database\Seeders;

use App\Models\Cliente;
use App\Models\Proyecto;
use App\Models\Tarea;
use App\Models\RegistroTiempo;
use App\Models\Comentario;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Auth;

class TicketingSystemSeeder extends Seeder
{
    public function run(): void
    {
        // Obtener usuarios existentes o crear algunos de prueba
        $usuarios = User::all();
        
        if ($usuarios->count() < 2) {
            // Crear usuarios de prueba si no existen
            $admin = User::firstOrCreate(
                ['email' => 'admin@test.com'],
                [
                    'name' => 'Miguel Angel',
                    'password' => bcrypt('password'),
                ]
            );
            
            $developer1 = User::firstOrCreate(
                ['email' => 'dev1@test.com'],
                [
                    'name' => 'Carlos Rodríguez',
                    'password' => bcrypt('password'),
                ]
            );
            
            $developer2 = User::firstOrCreate(
                ['email' => 'dev2@test.com'],
                [
                    'name' => 'Ana García',
                    'password' => bcrypt('password'),
                ]
            );
            
            $usuarios = collect([$admin, $developer1, $developer2]);
        }

        $admin = $usuarios->first();

        // Crear clientes
        $cliente1 = Cliente::create([
            'nombre' => 'TechCorp Solutions',
            'empresa' => 'TechCorp Inc.',
            'email' => 'contacto@techcorp.com',
            'telefono' => '+1234567890',
            'direccion' => 'Av. Tecnología 123, Ciudad',
            'estado' => 'activo',
        ]);

        $cliente2 = Cliente::create([
            'nombre' => 'Digital Ventures',
            'empresa' => 'Digital Ventures LLC',
            'email' => 'info@digitalventures.com',
            'telefono' => '+0987654321',
            'direccion' => 'Calle Innovación 456, Ciudad',
            'estado' => 'activo',
        ]);

        $cliente3 = Cliente::create([
            'nombre' => 'StartUp Creative',
            'empresa' => 'StartUp Creative SRL',
            'email' => 'hello@startupcreative.com',
            'telefono' => '+1122334455',
            'direccion' => 'Paseo Emprendedor 789, Ciudad',
            'estado' => 'activo',
        ]);

        // Crear proyectos
        $proyecto1 = Proyecto::create([
            'nombre' => 'Sistema de Gestión Empresarial',
            'descripcion' => 'Desarrollo de un ERP completo para la gestión de recursos empresariales',
            'estado' => 'activo',
            'cliente_id' => $cliente1->id,
            'fecha_inicio' => now()->subDays(30),
            'fecha_fin_estimada' => now()->addDays(60),
        ]);

        $proyecto2 = Proyecto::create([
            'nombre' => 'App Móvil de E-commerce',
            'descripcion' => 'Aplicación móvil para comercio electrónico con React Native',
            'estado' => 'activo',
            'cliente_id' => $cliente2->id,
            'fecha_inicio' => now()->subDays(20),
            'fecha_fin_estimada' => now()->addDays(40),
        ]);

        $proyecto3 = Proyecto::create([
            'nombre' => 'Portal Web Corporativo',
            'descripcion' => 'Rediseño completo del portal web corporativo con CMS',
            'estado' => 'activo',
            'cliente_id' => $cliente3->id,
            'fecha_inicio' => now()->subDays(10),
            'fecha_fin_estimada' => now()->addDays(30),
        ]);

        // Crear tareas para proyecto 1
        $tareas = [
            // Tareas pendientes
            [
                'titulo' => 'Diseño de base de datos',
                'descripcion' => 'Crear el diagrama ER y diseñar la estructura de la base de datos',
                'estado' => 'pendiente',
                'prioridad' => 'alta',
                'proyecto_id' => $proyecto1->id,
                'creador_id' => $admin->id,
                'responsable_id' => $usuarios->get(1)->id,
                'creado_por' => $admin->name,
                'creado_en' => now()->subDays(5),
                'modificado_por' => $admin->name,
                'modificado_en' => now()->subDays(5),
            ],
            [
                'titulo' => 'Configuración de entorno de desarrollo',
                'descripcion' => 'Instalar y configurar todas las herramientas necesarias',
                'estado' => 'pendiente',
                'prioridad' => 'media',
                'proyecto_id' => $proyecto1->id,
                'creador_id' => $admin->id,
                'responsable_id' => null,
                'creado_por' => $admin->name,
                'creado_en' => now()->subDays(6),
                'modificado_por' => $admin->name,
                'modificado_en' => now()->subDays(6),
            ],
            
            // Tareas en proceso
            [
                'titulo' => 'Desarrollo del módulo de usuarios',
                'descripcion' => 'Implementar CRUD de usuarios con autenticación y roles',
                'estado' => 'en_proceso',
                'prioridad' => 'urgente',
                'proyecto_id' => $proyecto1->id,
                'creador_id' => $admin->id,
                'responsable_id' => $usuarios->get(1)->id,
                'fecha_inicio' => now()->subDays(4),
                'creado_por' => $admin->name,
                'creado_en' => now()->subDays(5),
                'modificado_por' => $usuarios->get(1)->name,
                'modificado_en' => now()->subDays(4),
            ],
            [
                'titulo' => 'Implementar API REST',
                'descripcion' => 'Crear endpoints RESTful para todas las entidades principales',
                'estado' => 'en_proceso',
                'prioridad' => 'alta',
                'proyecto_id' => $proyecto1->id,
                'creador_id' => $admin->id,
                'responsable_id' => $usuarios->get(2)->id,
                'fecha_inicio' => now()->subDays(2),
                'creado_por' => $admin->name,
                'creado_en' => now()->subDays(3),
                'modificado_por' => $usuarios->get(2)->name,
                'modificado_en' => now()->subDays(2),
            ],
            
            // Tareas en revisión
            [
                'titulo' => 'Dashboard de administración',
                'descripcion' => 'Crear interfaz de dashboard con gráficos y estadísticas',
                'estado' => 'en_revision',
                'prioridad' => 'media',
                'proyecto_id' => $proyecto1->id,
                'creador_id' => $admin->id,
                'responsable_id' => $usuarios->get(1)->id,
                'fecha_inicio' => now()->subDays(8),
                'creado_por' => $admin->name,
                'creado_en' => now()->subDays(10),
                'modificado_por' => $usuarios->get(1)->name,
                'modificado_en' => now()->subDays(1),
            ],
            
            // Tareas finalizadas
            [
                'titulo' => 'Instalación de Laravel y dependencias',
                'descripcion' => 'Configurar proyecto Laravel con todas las dependencias necesarias',
                'estado' => 'finalizado',
                'prioridad' => 'alta',
                'proyecto_id' => $proyecto1->id,
                'creador_id' => $admin->id,
                'responsable_id' => $usuarios->get(2)->id,
                'fecha_inicio' => now()->subDays(24),
                'fecha_fin' => now()->subDays(23),
                'creado_por' => $admin->name,
                'creado_en' => now()->subDays(25),
                'modificado_por' => $usuarios->get(2)->name,
                'modificado_en' => now()->subDays(23),
            ],
            [
                'titulo' => 'Configuración de Git y repositorio',
                'descripcion' => 'Crear repositorio y configurar workflow de desarrollo',
                'estado' => 'finalizado',
                'prioridad' => 'media',
                'proyecto_id' => $proyecto1->id,
                'creador_id' => $admin->id,
                'responsable_id' => $usuarios->get(1)->id,
                'fecha_inicio' => now()->subDays(26),
                'fecha_fin' => now()->subDays(25),
                'creado_por' => $admin->name,
                'creado_en' => now()->subDays(26),
                'modificado_por' => $usuarios->get(1)->name,
                'modificado_en' => now()->subDays(25),
            ],

            // Tareas para proyecto 2
            [
                'titulo' => 'Diseño UI/UX de la app móvil',
                'descripcion' => 'Crear mockups y prototipos de todas las pantallas',
                'estado' => 'en_revision',
                'prioridad' => 'alta',
                'proyecto_id' => $proyecto2->id,
                'creador_id' => $admin->id,
                'responsable_id' => $usuarios->get(2)->id,
                'fecha_inicio' => now()->subDays(12),
                'creado_por' => $admin->name,
                'creado_en' => now()->subDays(15),
                'modificado_por' => $usuarios->get(2)->name,
                'modificado_en' => now()->subHours(6),
            ],
            [
                'titulo' => 'Integración con pasarela de pago',
                'descripcion' => 'Implementar Stripe y PayPal como métodos de pago',
                'estado' => 'en_proceso',
                'prioridad' => 'urgente',
                'proyecto_id' => $proyecto2->id,
                'creador_id' => $admin->id,
                'responsable_id' => $usuarios->get(1)->id,
                'fecha_inicio' => now()->subDays(5),
                'creado_por' => $admin->name,
                'creado_en' => now()->subDays(7),
                'modificado_por' => $usuarios->get(1)->name,
                'modificado_en' => now()->subDays(5),
            ],
            [
                'titulo' => 'Sistema de notificaciones push',
                'descripcion' => 'Configurar Firebase Cloud Messaging para notificaciones',
                'estado' => 'pendiente',
                'prioridad' => 'media',
                'proyecto_id' => $proyecto2->id,
                'creador_id' => $admin->id,
                'responsable_id' => null,
                'creado_por' => $admin->name,
                'creado_en' => now()->subDays(2),
                'modificado_por' => $admin->name,
                'modificado_en' => now()->subDays(2),
            ],

            // Tareas para proyecto 3
            [
                'titulo' => 'Migración de contenido antiguo',
                'descripcion' => 'Migrar todo el contenido del sitio anterior al nuevo CMS',
                'estado' => 'en_proceso',
                'prioridad' => 'alta',
                'proyecto_id' => $proyecto3->id,
                'creador_id' => $admin->id,
                'responsable_id' => $usuarios->get(2)->id,
                'fecha_inicio' => now()->subDays(6),
                'creado_por' => $admin->name,
                'creado_en' => now()->subDays(8),
                'modificado_por' => $usuarios->get(2)->name,
                'modificado_en' => now()->subDays(6),
            ],
            [
                'titulo' => 'Optimización SEO',
                'descripcion' => 'Implementar mejores prácticas SEO y optimizar velocidad',
                'estado' => 'pendiente',
                'prioridad' => 'media',
                'proyecto_id' => $proyecto3->id,
                'creador_id' => $admin->id,
                'responsable_id' => $usuarios->get(1)->id,
                'creado_por' => $admin->name,
                'creado_en' => now()->subDay(),
                'modificado_por' => $admin->name,
                'modificado_en' => now()->subDay(),
            ],
        ];

        foreach ($tareas as $tareaData) {
            $tarea = Tarea::create($tareaData);

            // Agregar algunos comentarios a tareas en proceso o en revisión
            if (in_array($tarea->estado, ['en_proceso', 'en_revision', 'finalizado'])) {
                Comentario::create([
                    'tarea_id' => $tarea->id,
                    'usuario_id' => $tarea->responsable_id,
                    'contenido' => 'He comenzado a trabajar en esta tarea. Cualquier duda la comento aquí.',
                ]);

                if ($tarea->estado === 'en_revision') {
                    Comentario::create([
                        'tarea_id' => $tarea->id,
                        'usuario_id' => $admin->id,
                        'contenido' => 'Por favor revisar los cambios realizados. Está listo para QA.',
                    ]);
                }
            }

            // Agregar registros de tiempo para tareas en proceso y finalizadas
            if (in_array($tarea->estado, ['en_proceso', 'finalizado'])) {
                RegistroTiempo::create([
                    'tarea_id' => $tarea->id,
                    'usuario_id' => $tarea->responsable_id,
                    'fecha_inicio' => now()->subHours(6),
                    'fecha_fin' => now()->subHours(4),
                    'nota' => 'Sesión de trabajo matutina',
                ]);

                RegistroTiempo::create([
                    'tarea_id' => $tarea->id,
                    'usuario_id' => $tarea->responsable_id,
                    'fecha_inicio' => now()->subHours(2),
                    'fecha_fin' => now()->subMinutes(30),
                    'nota' => 'Continuando con la implementación',
                ]);
            }
        }

        $this->command->info('✅ Sistema de ticketing poblado con datos de prueba');
        $this->command->info('📊 Creados: 3 clientes, 3 proyectos, ' . count($tareas) . ' tareas');
        $this->command->info('👥 Usuarios disponibles:');
        $this->command->info('   - admin@test.com / password (Miguel Angel - Administrador)');
        $this->command->info('   - dev1@test.com / password (Carlos Rodríguez - Desarrollador)');
        $this->command->info('   - dev2@test.com / password (Ana García - Desarrolladora)');
        $this->command->info('');
        $this->command->info('📝 Bitácora automática activada:');
        $this->command->info('   - Se registra automáticamente cada acción sobre las tareas');
        $this->command->info('   - Ver bitácora: GET /api/tareas/{id}/bitacora');
    }
}
