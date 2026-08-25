<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class StorageClean extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'storage:clean';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean all uploaded files in storage/app/public but keep .gitignore';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Limpiando archivos huérfanos en storage/app/public...");
        
        $directories = \Illuminate\Support\Facades\Storage::disk('public')->directories();
        $files = \Illuminate\Support\Facades\Storage::disk('public')->files();
        
        foreach ($directories as $dir) {
            \Illuminate\Support\Facades\Storage::disk('public')->deleteDirectory($dir);
            $this->line(" Eliminado directorio: {$dir}");
        }
        
        foreach ($files as $file) {
            if ($file !== '.gitignore') {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($file);
                $this->line(" Eliminado archivo: {$file}");
            }
        }
        
        $this->info("¡Limpieza de storage completada exitosamente!");
    }
}
