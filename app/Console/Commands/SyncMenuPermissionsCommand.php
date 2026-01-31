<?php

namespace App\Console\Commands;

use App\Services\MenuPermissionSyncService;
use Illuminate\Console\Command;

class SyncMenuPermissionsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permissions:sync-from-menus {--delete-orphans : Elimina permisos huérfanos sin preguntar}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sincroniza los permisos automáticamente desde la estructura del menú';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔄 Iniciando sincronización de permisos desde menús...');

        $deleteOrphans = (bool) $this->option('delete-orphans');

        // Si es interactivo, dejamos preguntar; si no, NO intentamos leer STDIN.
        if (!$deleteOrphans && $this->input->isInteractive() && \defined('STDIN')) {
            // Nota: el servicio reporta cuántos huérfanos existen, pero no los lista.
            // Para mantener este comando simple y seguro, solo preguntamos si quiere borrarlos.
            $deleteOrphans = $this->confirm('¿Deseas eliminar permisos huérfanos?', true);
        }

        $result = app(MenuPermissionSyncService::class)->sync(
            deleteOrphans: $deleteOrphans,
            assignAllToAdmin: true
        );

        $this->newLine();
        $this->info('✨ Sincronización completada:');
        $this->info('  • Permisos creados: '.$result['created']);
        $this->info('  • Permisos actualizados: '.$result['updated']);
        $this->info('  • Permisos huérfanos detectados: '.$result['orphaned']);
        $this->info('  • Total de permisos: '.$result['total_permissions']);
        
        return Command::SUCCESS;
    }
}
