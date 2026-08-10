<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('zonas_envio')) {
            Schema::create('zonas_envio', function (Blueprint $table) {
                $table->comment('Zonas geográficas de entrega con costo y tiempo');

                $table->id();
                $table->string('nombre', 150);
                $table->text('provincias')->nullable();
                $table->decimal('costo', 10, 2)->default(0);
                $table->string('tiempo_estimado', 100)->nullable();
                $table->boolean('activo')->default(true);
                $table->timestamp('creado_en')->useCurrent();
                $table->timestamp('actualizado_en')->useCurrent();
                $table->timestamp('created_at')->nullable();
                $table->timestamp('updated_at')->nullable();
            });

            DB::statement('ALTER TABLE public.zonas_envio ADD CONSTRAINT zonas_envio_costo_check CHECK (costo >= 0)');

            DB::statement('DROP TRIGGER IF EXISTS trg_upd_zonas_envio ON public.zonas_envio');
            DB::statement('CREATE TRIGGER trg_upd_zonas_envio BEFORE UPDATE ON public.zonas_envio FOR EACH ROW EXECUTE FUNCTION public.actualizar_timestamp()');
        }

        if (Schema::hasTable('zonas_envio') && Schema::hasTable('pedidos')) {
            Schema::table('pedidos', function (Blueprint $table) {
                $table->foreign('zona_envio_id', 'pedidos_zona_envio_id_fkey')->references('id')->on('zonas_envio')->onDelete('set null');
            });
        }

        if (Schema::hasTable('zonas_envio') && Schema::hasTable('promociones_envio_gratis')) {
            Schema::table('promociones_envio_gratis', function (Blueprint $table) {
                $table->foreign('zona_envio_id', 'promociones_envio_gratis_zona_envio_id_fkey')->references('id')->on('zonas_envio')->onDelete('set null');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('promociones_envio_gratis')) {
            Schema::table('promociones_envio_gratis', function (Blueprint $table) {
                $table->dropForeign('promociones_envio_gratis_zona_envio_id_fkey');
            });
        }

        if (Schema::hasTable('pedidos')) {
            Schema::table('pedidos', function (Blueprint $table) {
                $table->dropForeign('pedidos_zona_envio_id_fkey');
            });
        }

        DB::statement('DROP TRIGGER IF EXISTS trg_upd_zonas_envio ON public.zonas_envio');

        Schema::dropIfExists('zonas_envio');
    }
};