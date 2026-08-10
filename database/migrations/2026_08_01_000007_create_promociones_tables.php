<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('cupones')) {
            Schema::create('cupones', function (Blueprint $table) {
                $table->comment('Cupones de descuento: porcentaje, monto fijo o envío gratis');

                $table->id();
                $table->string('codigo', 50);
                $table->string('tipo', 30);
                $table->decimal('valor', 10, 2);
                $table->decimal('monto_minimo', 10, 2)->default(0);
                $table->integer('maximo_usos_total')->nullable();
                $table->integer('usos_por_cliente')->default(1);
                $table->integer('usos_actuales')->default(0);
                $table->boolean('activo')->default(true);
                $table->timestamp('inicio_en')->nullable();
                $table->timestamp('fin_en')->nullable();
                $table->string('aplica_a', 30)->default('catalogo');
                $table->bigInteger('categoria_id')->nullable();
                $table->bigInteger('producto_id')->nullable();
                $table->timestamp('creado_en')->useCurrent();
                $table->timestamp('actualizado_en')->useCurrent();

                $table->unique(['codigo'], 'cupones_codigo_key');
                $table->index(['codigo'], 'idx_cupones_codigo');
                $table->index(['activo'], 'idx_cupones_activo');
                $table->foreign('categoria_id', 'cupones_categoria_id_fkey')->references('id')->on('categorias')->onDelete('set null');
                $table->foreign('producto_id', 'cupones_producto_id_fkey')->references('id')->on('productos')->onDelete('set null');
            });

            DB::statement('ALTER TABLE public.cupones ADD CONSTRAINT cupones_aplica_a_check CHECK ((aplica_a)::text = ANY ((ARRAY[(\'catalogo\'::character varying), (\'categoria\'::character varying), (\'producto\'::character varying)])::text[]))');
            DB::statement('ALTER TABLE public.cupones ADD CONSTRAINT cupones_tipo_check CHECK ((tipo)::text = ANY ((ARRAY[(\'porcentaje\'::character varying), (\'monto_fijo\'::character varying), (\'envio_gratis\'::character varying)])::text[]))');
            DB::statement('ALTER TABLE public.cupones ADD CONSTRAINT cupones_valor_check CHECK (valor > 0)');

            DB::statement('DROP TRIGGER IF EXISTS trg_upd_cupones ON public.cupones');
            DB::statement('CREATE TRIGGER trg_upd_cupones BEFORE UPDATE ON public.cupones FOR EACH ROW EXECUTE FUNCTION public.actualizar_timestamp()');
        }

        Schema::table('carritos', function (Blueprint $table) {
            $table->foreign('cupon_id', 'carritos_cupon_id_fkey')->references('id')->on('cupones')->onDelete('set null');
        });

        Schema::table('pedidos', function (Blueprint $table) {
            $table->foreign('cupon_id', 'pedidos_cupon_id_fkey')->references('id')->on('cupones')->onDelete('set null');
        });

        if (!Schema::hasTable('usos_cupon')) {
            Schema::create('usos_cupon', function (Blueprint $table) {
                $table->comment('Trazabilidad de uso de cupones por pedido y usuario');

                $table->id();
                $table->bigInteger('cupon_id');
                $table->bigInteger('usuario_id');
                $table->bigInteger('pedido_id');
                $table->decimal('descuento_aplicado', 10, 2)->default(0);
                $table->timestamp('creado_en')->useCurrent();

                $table->unique(['cupon_id', 'pedido_id'], 'usos_cupon_cupon_id_pedido_id_key');
                $table->foreign('cupon_id', 'usos_cupon_cupon_id_fkey')->references('id')->on('cupones');
                $table->foreign('pedido_id', 'usos_cupon_pedido_id_fkey')->references('id')->on('pedidos');
                $table->foreign('usuario_id', 'usos_cupon_usuario_id_fkey')->references('id')->on('usuarios');
        });
        }

        if (!Schema::hasTable('promociones_envio_gratis')) {
            Schema::create('promociones_envio_gratis', function (Blueprint $table) {
                $table->comment('Promociones de envío gratuito por zona y monto mínimo');

                $table->id();
                $table->bigInteger('zona_envio_id')->nullable();
                $table->decimal('monto_minimo', 10, 2)->default(0);
                $table->timestamp('inicio_en');
                $table->timestamp('fin_en');
                $table->boolean('activo')->default(true);
                $table->timestamp('creado_en')->useCurrent();
        });
        }

    }

    public function down(): void
    {
        Schema::table('carritos', function (Blueprint $table) {
            $table->dropForeign('carritos_cupon_id_fkey');
        });

        Schema::table('pedidos', function (Blueprint $table) {
            $table->dropForeign('pedidos_cupon_id_fkey');
        });

        DB::statement('DROP TRIGGER IF EXISTS trg_upd_cupones ON public.cupones');

        Schema::dropIfExists('promociones_envio_gratis');
        Schema::dropIfExists('usos_cupon');
        Schema::dropIfExists('cupones');
    }
};
