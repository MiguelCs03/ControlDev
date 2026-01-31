<?php

namespace App\Services;

use App\Models\Menu;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Submenu;
use App\Models\Subsubmenu;

class MenuPermissionSyncService
{
    public function sync(bool $deleteOrphans = false, bool $assignAllToAdmin = true): array
    {
        $menuSections = [];

        $createdCount = 0;
        $updatedCount = 0;

        $menus = Menu::all();

        foreach ($menus as $menu) {
            if ($menu->section || $menu->route) {
                $section = $menu->section ?: str_replace('/', '', (string) $menu->route);
                if (!empty($section)) {
                    $menuSections[$section] = [
                        'name' => $menu->name,
                        'section' => $section,
                        'type' => 'menu',
                        'route' => $menu->route,
                    ];
                }
            }

            $submenus = Submenu::where('menu_id', $menu->id)->get();

            foreach ($submenus as $submenu) {
                $section = $submenu->section ?: str_replace('/', '', (string) $submenu->route);
                if (!empty($section)) {
                    $menuSections[$section] = [
                        'name' => $submenu->name,
                        'section' => $section,
                        'type' => 'submenu',
                        'route' => $submenu->route,
                        'parent_menu' => $menu->name,
                    ];
                }

                $subsubmenus = Subsubmenu::where('submenu_id', $submenu->id)->get();

                foreach ($subsubmenus as $subsubmenu) {
                    $section = $subsubmenu->section ?: str_replace('/', '', (string) $subsubmenu->route);
                    if (!empty($section)) {
                        $menuSections[$section] = [
                            'name' => $subsubmenu->name,
                            'section' => $section,
                            'type' => 'subsubmenu',
                            'route' => $subsubmenu->route,
                            'parent_menu' => $menu->name,
                            'parent_submenu' => $submenu->name,
                        ];
                    }
                }
            }
        }

        foreach ($menuSections as $section => $data) {
            $permission = Permission::where('subject', $section)->first();

            $newDescription = 'Acceso a '.$data['name']
                .(isset($data['parent_menu']) ? ' ('.$data['parent_menu'].')' : '');

            if (!$permission) {
                Permission::create([
                    'name' => $data['name'],
                    'action' => 'read',
                    'subject' => $section,
                    'description' => $newDescription,
                ]);
                $createdCount++;
            } else {
                $updated = false;

                if ($permission->name !== $data['name']) {
                    $permission->name = $data['name'];
                    $updated = true;
                }

                if ($permission->description !== $newDescription) {
                    $permission->description = $newDescription;
                    $updated = true;
                }

                if ($updated) {
                    $permission->save();
                    $updatedCount++;
                }
            }
        }

        $validSections = array_keys($menuSections);

        $orphanedCount = 0;
        if (!empty($validSections)) {
            $orphanedCount = Permission::whereNotIn('subject', $validSections)->count();
            if ($deleteOrphans && $orphanedCount > 0) {
                Permission::whereNotIn('subject', $validSections)->delete();
            }
        }

        if ($assignAllToAdmin) {
            $adminRole = Role::whereRaw('LOWER(nombre) = ?', ['admin'])->first();
            if ($adminRole) {
                $permissionIds = Permission::pluck('id')->all();
                $adminRole->permissions()->sync($permissionIds);
            }
        }

        return [
            'created' => $createdCount,
            'updated' => $updatedCount,
            'orphaned' => $orphanedCount,
            'total_permissions' => Permission::count(),
        ];
    }
}
