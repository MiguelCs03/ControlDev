<?php

namespace App\Observers;

use App\Models\Submenu;
use App\Services\MenuPermissionSyncService;

class SubmenuObserver
{
    /**
     * Handle the Submenu "created" event.
     */
    public function created(Submenu $submenu): void
    {
        $this->syncPermissions();
    }

    /**
     * Handle the Submenu "updated" event.
     */
    public function updated(Submenu $submenu): void
    {
        $this->syncPermissions();
    }

    /**
     * Handle the Submenu "deleted" event.
     */
    public function deleted(Submenu $submenu): void
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
