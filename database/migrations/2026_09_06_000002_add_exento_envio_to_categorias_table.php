<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('categorias') && !Schema::hasColumn('categorias', 'exento_envio')) {
            Schema::table('categorias', function (Blueprint $table) {
                $table->boolean('exento_envio')
                    ->default(false)
                    ->comment('Si es true, los productos de esta categoría no generan costo de flete físico');
            });
        }

        // Marcar categorías y subcategorías de servicios / software existentes como exentas
        $slugsExentos = [
            'servicios-informaticos',
            'software-y-licencias',
            'armado-de-pc',
            'mantenimiento-y-limpieza-de-equipos',
            'instalacion-de-redes',
            'recuperacion-de-datos',
            'formateo-e-instalacion-de-software',
            'soporte-tecnico-a-domicilio',
        ];

        DB::table('categorias')
            ->whereIn('slug', $slugsExentos)
            ->update(['exento_envio' => true]);

        // También marcar cualquier hija directa de 'servicios-informaticos' o 'software-y-licencias'
        $padresIds = DB::table('categorias')
            ->whereIn('slug', ['servicios-informaticos', 'software-y-licencias'])
            ->pluck('id');

        if ($padresIds->isNotEmpty()) {
            DB::table('categorias')
                ->whereIn('padre_id', $padresIds)
                ->update(['exento_envio' => true]);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('categorias') && Schema::hasColumn('categorias', 'exento_envio')) {
            Schema::table('categorias', function (Blueprint $table) {
                $table->dropColumn('exento_envio');
            });
        }
    }
};
