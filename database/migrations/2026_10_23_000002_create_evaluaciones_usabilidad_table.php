<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ejecuta las migraciones para crear la tabla de métricas y evaluación de usabilidad (CES & CSAT).
     */
    public function up(): void
    {
        if (!Schema::hasTable('evaluaciones_usabilidad')) {
            Schema::create('evaluaciones_usabilidad', function (Blueprint $table) {
                $table->comment('Métricas de usabilidad post-compra: Customer Effort Score (CES), CSAT y tiempos');

                $table->id();
                $table->bigInteger('pedido_id');
                $table->bigInteger('usuario_id')->nullable();
                $table->smallInteger('puntuacion_facilidad'); // 1 a 5 (Customer Effort Score - CES)
                $table->smallInteger('puntuacion_general')->nullable(); // 1 a 5 (CSAT)
                $table->integer('tiempo_checkout_segundos')->nullable(); // Eficiencia (Time-to-checkout)
                $table->text('comentario')->nullable(); // Feedback cualitativo
                $table->string('dispositivo', 40)->nullable(); // Desktop, Mobile, Tablet
                $table->string('navegador', 60)->nullable();
                $table->timestamp('creado_en')->useCurrent();
                $table->timestamp('actualizado_en')->useCurrent();

                // Llaves foráneas
                $table->foreign('pedido_id', 'fk_usabilidad_pedido')
                    ->references('id')
                    ->on('pedidos')
                    ->onDelete('cascade');

                $table->foreign('usuario_id', 'fk_usabilidad_usuario')
                    ->references('id')
                    ->on('usuarios')
                    ->onDelete('set null');

                // Índices
                $table->unique('pedido_id', 'uk_usabilidad_pedido');
                $table->index('puntuacion_facilidad', 'idx_usabilidad_facilidad');
                $table->index('creado_en', 'idx_usabilidad_creado_en');
            });

            // Restricción para puntuación entre 1 y 5
            DB::statement('ALTER TABLE public.evaluaciones_usabilidad ADD CONSTRAINT chk_usabilidad_facilidad CHECK (puntuacion_facilidad >= 1 AND puntuacion_facilidad <= 5)');

            // Trigger para actualizar timestamp
            DB::statement('DROP TRIGGER IF EXISTS trg_upd_usabilidad ON public.evaluaciones_usabilidad');
            DB::statement('CREATE TRIGGER trg_upd_usabilidad BEFORE UPDATE ON public.evaluaciones_usabilidad FOR EACH ROW EXECUTE FUNCTION public.actualizar_timestamp()');
        }
    }

    /**
     * Revierte las migraciones.
     */
    public function down(): void
    {
        DB::statement('DROP TRIGGER IF EXISTS trg_upd_usabilidad ON public.evaluaciones_usabilidad');
        Schema::dropIfExists('evaluaciones_usabilidad');
    }
};
