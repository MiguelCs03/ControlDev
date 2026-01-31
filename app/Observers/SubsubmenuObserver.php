<?php

namespace App\Observers;

use App\Models\Subsubmenu;
use App\Services\MenuPermissionSyncService;

class SubsubmenuObserver
{
    /**
     * Handle the Subsubmenu "created" event.
     */
    public function created(Subsubmenu $subsubmenu): void
    {
        $this->syncPermissions();
    }

    /**
     * Handle the Subsubmenu "updated" event.
     */
    public function updated(Subsubmenu $subsubmenu): void
    {
        $this->syncPermissions();
    }

    /**
     * Handle the Subsubmenu "deleted" event.
     */
    public function deleted(Subsubmenu $subsubmenu): void
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
