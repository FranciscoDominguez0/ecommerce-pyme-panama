<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class StorageClean extends Command
{
    /**
     * Nombre y firma del comando de consola.
     *
     * @var string
     */
    protected $signature = 'storage:clean';

    /**
     * Descripción del comando de consola.
     *
     * @var string
     */
    protected $description = 'Elimina archivos y directorios subidos en storage/app/public conservando .gitignore';

    /**
     * Ejecuta la limpieza de archivos en disco público.
     */
    public function handle(): void
    {
        $this->info("Limpiando archivos huérfanos en storage/app/public...");
        
        $disco = Storage::disk('public');
        $directories = $disco->directories();
        $files = $disco->files();
        
        foreach ($directories as $dir) {
            $disco->deleteDirectory($dir);
            $this->line(" Eliminado directorio: {$dir}");
        }
        
        foreach ($files as $file) {
            if ($file !== '.gitignore') {
                $disco->delete($file);
                $this->line(" Eliminado archivo: {$file}");
            }
        }
        
        $this->info("¡Limpieza de storage completada exitosamente!");
    }
}
