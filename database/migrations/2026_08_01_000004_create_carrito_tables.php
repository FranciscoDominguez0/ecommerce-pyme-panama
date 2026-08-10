<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('carritos', function (Blueprint $table) {
            $table->comment('Carritos persistentes: sesión para visitantes, usuario_id para logueados');

            $table->id();
            $table->bigInteger('usuario_id')->nullable();
            $table->bigInteger('cupon_id')->nullable();
            $table->string('sesion_id', 255)->nullable();
            $table->decimal('descuento_aplicado', 10, 2)->default(0);
            $table->timestamp('creado_en')->useCurrent();
            $table->timestamp('actualizado_en')->useCurrent();

            $table->index(['usuario_id'], 'idx_carritos_usuario');
            $table->index(['sesion_id'], 'idx_carritos_sesion');
            $table->foreign('usuario_id', 'carritos_usuario_id_fkey')->references('id')->on('usuarios')->onDelete('cascade');
        });

        // carritos_cupon_id_fkey se agrega en 2026_08_01_000007 (cupones se crea en ese módulo, que corre después de esta migración).
        DB::statement('ALTER TABLE public.carritos ADD CONSTRAINT carrito_owner CHECK ((usuario_id IS NOT NULL) OR (sesion_id IS NOT NULL))');

        DB::statement('DROP TRIGGER IF EXISTS trg_upd_carritos ON public.carritos');
        DB::statement('CREATE TRIGGER trg_upd_carritos BEFORE UPDATE ON public.carritos FOR EACH ROW EXECUTE FUNCTION public.actualizar_timestamp()');

        Schema::create('items_carrito', function (Blueprint $table) {
            $table->comment('Productos dentro del carrito con precio congelado');

            $table->id();
            $table->bigInteger('carrito_id');
            $table->bigInteger('producto_id');
            $table->bigInteger('variante_producto_id')->nullable();
            $table->integer('cantidad');
            $table->decimal('precio_unitario', 10, 2);
            $table->timestamp('creado_en')->useCurrent();
            $table->timestamp('actualizado_en')->useCurrent();

            $table->unique(['carrito_id', 'producto_id', 'variante_producto_id'], 'items_carrito_carrito_id_producto_id_variante_producto_id_key');
            $table->index(['carrito_id'], 'idx_items_carrito_carrito');
            $table->foreign('carrito_id', 'items_carrito_carrito_id_fkey')->references('id')->on('carritos')->onDelete('cascade');
            $table->foreign('producto_id', 'items_carrito_producto_id_fkey')->references('id')->on('productos')->onDelete('cascade');
            $table->foreign('variante_producto_id', 'items_carrito_variante_producto_id_fkey')->references('id')->on('variantes_producto')->onDelete('set null');
        });

        DB::statement('ALTER TABLE public.items_carrito ADD CONSTRAINT items_carrito_cantidad_check CHECK (cantidad > 0)');
        DB::statement('ALTER TABLE public.items_carrito ADD CONSTRAINT items_carrito_precio_unitario_check CHECK (precio_unitario >= 0)');

        DB::statement('DROP TRIGGER IF EXISTS trg_upd_items_carrito ON public.items_carrito');
        DB::statement('CREATE TRIGGER trg_upd_items_carrito BEFORE UPDATE ON public.items_carrito FOR EACH ROW EXECUTE FUNCTION public.actualizar_timestamp()');

        Schema::create('lista_deseos', function (Blueprint $table) {
            $table->comment('Productos guardados en lista de deseos por usuario');

            $table->bigInteger('usuario_id');
            $table->bigInteger('producto_id');
            $table->timestamp('creado_en')->useCurrent();

            $table->primary(['usuario_id', 'producto_id']);
            $table->foreign('usuario_id', 'lista_deseos_usuario_id_fkey')->references('id')->on('usuarios')->onDelete('cascade');
            $table->foreign('producto_id', 'lista_deseos_producto_id_fkey')->references('id')->on('productos')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        DB::statement('DROP TRIGGER IF EXISTS trg_upd_carritos ON public.carritos');
        DB::statement('DROP TRIGGER IF EXISTS trg_upd_items_carrito ON public.items_carrito');

        Schema::dropIfExists('lista_deseos');
        Schema::dropIfExists('items_carrito');
        Schema::dropIfExists('carritos');
    }
};
