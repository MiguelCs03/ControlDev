<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Ejecutar el comando de sincronización de permisos desde menús
        $this->command->info('🔄 Sincronizando permisos desde menús...');
        \Artisan::call('permissions:sync-from-menus');
        $this->command->info(\Artisan::output());

        // Asignar todos los permisos al rol admin si existe
        $adminRole = Role::where('nombre', 'admin')->first();
        if ($adminRole) {
            $permissionIds = Permission::pluck('id')->all();
            $adminRole->permissions()->sync($permissionIds);
            $this->command->info('✅ Todos los permisos asignados al rol admin');
        }
    }
}
