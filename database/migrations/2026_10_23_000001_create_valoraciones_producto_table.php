<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ejecuta las migraciones para crear la tabla de valoraciones de productos.
     */
    public function up(): void
    {
        if (!Schema::hasTable('valoraciones_producto')) {
            Schema::create('valoraciones_producto', function (Blueprint $table) {
                $table->comment('Valoraciones y opiniones de productos con compra verificada en pedidos entregados');

                $table->id();
                $table->bigInteger('producto_id');
                $table->bigInteger('pedido_id');
                $table->bigInteger('usuario_id');
                $table->smallInteger('calificacion');
                $table->string('titulo', 120)->nullable();
                $table->text('comentario')->nullable();
                $table->boolean('es_aprobado')->default(true);
                $table->timestamp('creado_en')->useCurrent();
                $table->timestamp('actualizado_en')->useCurrent();

                // Llaves foráneas
                $table->foreign('producto_id', 'fk_valoraciones_producto')
                    ->references('id')
                    ->on('productos')
                    ->onDelete('cascade');

                $table->foreign('pedido_id', 'fk_valoraciones_pedido')
                    ->references('id')
                    ->on('pedidos')
                    ->onDelete('cascade');

                $table->foreign('usuario_id', 'fk_valoraciones_usuario')
                    ->references('id')
                    ->on('usuarios')
                    ->onDelete('cascade');

                // Índices
                $table->unique(['pedido_id', 'producto_id'], 'uk_valoracion_pedido_producto');
                $table->index(['producto_id', 'es_aprobado'], 'idx_valoraciones_producto_aprobado');
                $table->index(['usuario_id'], 'idx_valoraciones_usuario');
                $table->index(['creado_en'], 'idx_valoraciones_creado_en');
            });

            // Restricción para asegurar calificación entre 1 y 5 estrellas
            DB::statement('ALTER TABLE public.valoraciones_producto ADD CONSTRAINT chk_valoraciones_calificacion CHECK (calificacion >= 1 AND calificacion <= 5)');

            // Trigger para actualizar el timestamp en actualizaciones
            DB::statement('DROP TRIGGER IF EXISTS trg_upd_valoraciones ON public.valoraciones_producto');
            DB::statement('CREATE TRIGGER trg_upd_valoraciones BEFORE UPDATE ON public.valoraciones_producto FOR EACH ROW EXECUTE FUNCTION public.actualizar_timestamp()');
        }
    }

    /**
     * Revierte las migraciones.
     */
    public function down(): void
    {
        DB::statement('DROP TRIGGER IF EXISTS trg_upd_valoraciones ON public.valoraciones_producto');
        Schema::dropIfExists('valoraciones_producto');
    }
};
