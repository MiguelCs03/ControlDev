<?php
namespace App\Http\Controllers;

use App\Models\Menu;
use App\Services\MenuPermissionSyncService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class MenuController extends Controller
{
    // Devuelve todos los menús con sus submenús activos y ordenados
    // FILTRADOS según los permisos del usuario actual (si está autenticado)
    public function index(Request $request)
    {
        // Si insertan menús/submenús directo en BD, no disparan observers.
        // Sincronizamos aquí de forma segura y con throttle.
        try {
            $lastSyncAt = Cache::get('menu_permissions_last_sync_at');
            $shouldSync = !$lastSyncAt || (time() - (int) $lastSyncAt) >= 5;

            if ($shouldSync) {
                app(MenuPermissionSyncService::class)->sync(deleteOrphans: false, assignAllToAdmin: true);
                Cache::put('menu_permissions_last_sync_at', time(), 60);
            }
        } catch (\Throwable $e) {
            Log::warning('Error sincronizando permisos desde menús (MenuController): '.$e->getMessage());
        }

        // Intentar obtener usuario autenticado (puede ser null si no hay sesión activa)
        $user = $request->user();
        
        // Log para depuración
        $permissions = [];
        $allowedSections = [];
        $isAdmin = false;
        
        if ($user) {
            // Cargar roles y permisos del usuario actual
            $user->loadMissing('roles.permissions');
            
            // Verificar si es admin (múltiples variantes posibles)
            $adminRoles = ['admin', 'administrador', 'administrator'];
            foreach ($user->roles as $role) {
                if (in_array(strtolower($role->nombre), $adminRoles)) {
                    $isAdmin = true;
                    break;
                }
            }
            
            // Si es admin, no filtrar nada - tiene acceso a todo
            if (!$isAdmin) {
                foreach ($user->roles as $role) {
                    foreach ($role->permissions as $permission) {
                        $permissions[] = $permission->name;
                        // Usar subject como sección permitida; fallback al name si no hay subject
                        $section = $permission->subject ?: $permission->name;
                        if (!empty($section)) {
                            $allowedSections[] = trim(strtolower($section));
                        }
                    }
                }
                
                // Asegurar que dashboard-inicio siempre sea accesible
                $allowedSections[] = 'dashboard-inicio';
                $allowedSections = array_unique(array_map('strtolower', $allowedSections));
            }
        }
        
        // Log para depuración
        Log::debug('Cargando menús para usuario', [
            'user_id' => $user?->id,
            'is_admin' => $isAdmin,
            'permissions' => $permissions,
            'allowed_sections' => $allowedSections,
        ]);
        
        // Obtener menús activos
        $menus = Menu::with(['submenus' => function($q) {
            $q->where('is_active', true)
                ->orderBy('order')
                ->with(['subsubmenus' => function ($qq) {
                    $qq->where('is_active', true)->orderBy('order');
                }]);
        }])
        ->where('is_active', true)
        ->orderBy('order')
        ->get();
        
        // Normalizar los campos de section
        $menus->each(function ($menu) {
            // Si no tiene sección definida, usar la ruta o el nombre como sección
            if (empty($menu->section)) {
                $routeKey = str_replace('/', '', $menu->route ?? '');
                $menu->section = $routeKey ?: strtolower($menu->name);
            }
            
            // Hacer lo mismo para los submenús
            $menu->submenus->each(function ($submenu) {
                if (empty($submenu->section)) {
                    $routeKey = str_replace('/', '', $submenu->route ?? '');
                    $submenu->section = $routeKey ?: strtolower($submenu->name);
                }

                // Y para el tercer nivel
                $submenu->subsubmenus?->each(function ($subsubmenu) {
                    if (empty($subsubmenu->section)) {
                        $routeKey = str_replace('/', '', $subsubmenu->route ?? '');
                        $subsubmenu->section = $routeKey ?: strtolower($subsubmenu->name);
                    }
                });
            });
        });
        
        // Filtrar menús según permisos del usuario
        if (!$user) {
            // Si no hay usuario autenticado, devolver array vacío
            $menus = collect([]);
        } elseif (!$isAdmin && count($allowedSections) > 0) {
            // Solo filtrar si NO es admin y tiene secciones permitidas
            $menus = $menus->filter(function ($menu) use ($allowedSections) {
                // Verificar si el menú principal tiene permisos
                $menuSection = strtolower($menu->section ?? '');
                $hasMenuPermission = in_array($menuSection, $allowedSections);
                
                // Filtrar submenús según permisos
                $menu->submenus = $menu->submenus->filter(function ($submenu) use ($allowedSections) {
                    $submenuSection = strtolower($submenu->section ?? '');
                    $hasSubmenuPermission = in_array($submenuSection, $allowedSections);
                    
                    // Filtrar subsubmenús según permisos
                    if ($submenu->subsubmenus) {
                        $submenu->subsubmenus = $submenu->subsubmenus->filter(function ($subsubmenu) use ($allowedSections) {
                            $subsubmenuSection = strtolower($subsubmenu->section ?? '');
                            return in_array($subsubmenuSection, $allowedSections);
                        });
                    }
                    
                    return $hasSubmenuPermission || ($submenu->subsubmenus && $submenu->subsubmenus->count() > 0);
                });
                
                // Mantener el menú si tiene permiso directo O si tiene submenús con permisos
                return $hasMenuPermission || $menu->submenus->count() > 0;
            })->values();
        }
        // Si es admin, devolver todos los menús sin filtrar
        
        return response()->json($menus);
    }
}
