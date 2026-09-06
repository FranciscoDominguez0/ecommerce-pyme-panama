<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('direcciones') && !Schema::hasColumn('direcciones', 'zona_envio_id')) {
            Schema::table('direcciones', function (Blueprint $table) {
                $table->foreignId('zona_envio_id')->nullable()->constrained('zonas_envio')->nullOnDelete();
            });
        }

        if (Schema::hasTable('productos') && !Schema::hasColumn('productos', 'es_digital')) {
            Schema::table('productos', function (Blueprint $table) {
                $table->boolean('es_digital')->default(false)->comment('Si es true, es software, licencia o servicio sin costo de envío físico');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('direcciones') && Schema::hasColumn('direcciones', 'zona_envio_id')) {
            Schema::table('direcciones', function (Blueprint $table) {
                $table->dropConstrainedForeignId('zona_envio_id');
            });
        }

        if (Schema::hasTable('productos') && Schema::hasColumn('productos', 'es_digital')) {
            Schema::table('productos', function (Blueprint $table) {
                $table->dropColumn('es_digital');
            });
        }
    }
};
