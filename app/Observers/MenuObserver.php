<?php

namespace App\Observers;

use App\Models\Menu;
use App\Services\MenuPermissionSyncService;

class MenuObserver
{
    /**
     * Handle the Menu "created" event.
     */
    public function created(Menu $menu): void
    {
        $this->syncPermissions();
    }

    /**
     * Handle the Menu "updated" event.
     */
    public function updated(Menu $menu): void
    {
        $this->syncPermissions();
    }

    /**
     * Handle the Menu "deleted" event.
     */
    public function deleted(Menu $menu): void
    {
        $this->syncPermissions();
    }

    /**
     * Sincronizar permisos desde menús
     */
    private function syncPermissions(): void
    {
        try {
            app(MenuPermissionSyncService::class)->sync(deleteOrphans: false, assignAllToAdmin: true);
        } catch (\Exception $e) {
            \Log::error('Error sincronizando permisos desde menús: ' . $e->getMessage());
        }
    }
}
