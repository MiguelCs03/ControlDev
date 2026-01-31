<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Role;
use App\Services\MenuPermissionSyncService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    // Listar todos los permisos
    public function index()
    {
        return response()->json(Permission::all());
    }

    // Listar permisos asignados a un rol
    public function byRole($roleId)
    {
        $role = Role::with('permissions')->findOrFail($roleId);
        return response()->json([
            'role' => $role->only(['id','nombre']),
            'permissions' => $role->permissions->pluck('id'),
        ]);
    }

    // Sincronizar permisos de un rol
    public function syncForRole(Request $request, $roleId)
    {
        $data = $request->validate([
            'permission_ids' => 'array',
            'permission_ids.*' => 'integer|exists:permissions,id',
            'tu_inicio' => 'nullable|string|max:255', // Validar vista inicial
        ]);

        $role = Role::findOrFail($roleId);
        $role->permissions()->sync($data['permission_ids'] ?? []);
        
        // Actualizar campo tu_inicio si se envió
        if (isset($data['tu_inicio'])) {
            $role->tu_inicio = $data['tu_inicio'];
            $role->save();
        }

        return response()->json(['message' => 'Permisos y configuración del rol actualizados correctamente']);
    }

    // Listar permisos agrupados por la estructura del menú
    // NOTA: Este endpoint muestra TODOS los menús (activos e inactivos)
    // para que aparezcan en el modal de permisos, incluso si no están visibles en la barra lateral
    public function indexFromMenus()
    {
        // SINCRONIZACIÓN AUTOMÁTICA (WEB-SAFE):
        // Si insertan menús/submenús directo en BD (SQL), no disparan observers.
        // Por eso sincronizamos aquí, sin Artisan (evita STDIN/confirm en HTTP).
        // Throttle simple para no ejecutar en cada request.
        try {
            $lastSyncAt = Cache::get('menu_permissions_last_sync_at');
            $shouldSync = !$lastSyncAt || (time() - (int) $lastSyncAt) >= 5;

            if ($shouldSync) {
                app(MenuPermissionSyncService::class)->sync(deleteOrphans: false, assignAllToAdmin: true);
                Cache::put('menu_permissions_last_sync_at', time(), 60);
            }
        } catch (\Throwable $e) {
            \Log::warning('Error en sincronización automática de permisos: ' . $e->getMessage());
        }
        
        $menus = \App\Models\Menu::with(['submenus' => function($q) {
            $q->orderBy('order')
                ->with(['subsubmenus' => function ($qq) {
                    $qq->orderBy('order');
                }]);
        }])
        ->orderBy('order')
        ->get();

        $result = [];

        foreach ($menus as $menu) {
            $menuData = [
                'menu_id' => $menu->id,
                'menu_name' => $menu->name,
                'menu_icon' => $menu->icon,
                'is_active' => $menu->is_active,
                'permissions' => []
            ];

            // Agregar permiso del menú principal si tiene route/section
            if ($menu->section || $menu->route) {
                $section = $menu->section ?: str_replace('/', '', $menu->route);
                $permission = Permission::where('subject', $section)->first();
                if ($permission) {
                    $menuData['permissions'][] = [
                        'id' => $permission->id,
                        'name' => $permission->name,
                        'subject' => $permission->subject,
                        'type' => 'menu',
                        'route' => $menu->route,
                        'is_active' => $menu->is_active,
                    ];
                }
            }

            // Agregar permisos de submenús
            foreach ($menu->submenus as $submenu) {
                $section = $submenu->section ?: str_replace('/', '', $submenu->route);
                $permission = Permission::where('subject', $section)->first();
                if ($permission) {
                    $menuData['permissions'][] = [
                        'id' => $permission->id,
                        'name' => $permission->name,
                        'subject' => $permission->subject,
                        'type' => 'submenu',
                        'route' => $submenu->route,
                        'is_active' => $submenu->is_active,
                    ];
                }

                // Agregar permisos de subsubmenús
                if ($submenu->subsubmenus) {
                    foreach ($submenu->subsubmenus as $subsubmenu) {
                        $section = $subsubmenu->section ?: str_replace('/', '', $subsubmenu->route);
                        $permission = Permission::where('subject', $section)->first();
                        if ($permission) {
                            $menuData['permissions'][] = [
                                'id' => $permission->id,
                                'name' => $permission->name,
                                'subject' => $permission->subject,
                                'type' => 'subsubmenu',
                                'route' => $subsubmenu->route,
                                'parent_submenu' => $submenu->name,
                                'is_active' => $subsubmenu->is_active,
                            ];
                        }
                    }
                }
            }

            // Agregar el menú SIEMPRE, incluso si no tiene permisos aún
            // (para que se vean los nuevos menús sin permisos en el modal)
            $result[] = $menuData;
        }

        return response()->json($result);
    }
}
