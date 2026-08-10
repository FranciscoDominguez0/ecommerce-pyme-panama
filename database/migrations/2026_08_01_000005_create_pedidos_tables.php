<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $fnNumero = DB::select("SELECT 1 FROM pg_proc WHERE proname = 'generar_numero_pedido'");
        if (empty($fnNumero)) {
            DB::unprepared(<<<'SQL'
CREATE FUNCTION public.generar_numero_pedido()
RETURNS trigger
LANGUAGE plpgsql
AS $$ BEGIN
    NEW.numero_pedido := 'P-' || TO_CHAR(NOW(), 'YYYY') || '-' || LPAD(NEW.id::TEXT, 6, '0');
    RETURN NEW;
END $$;
SQL);
        }

        $fnEstado = DB::select("SELECT 1 FROM pg_proc WHERE proname = 'registrar_estado_inicial_pedido'");
        if (empty($fnEstado)) {
            DB::unprepared(<<<'SQL'
CREATE FUNCTION public.registrar_estado_inicial_pedido()
RETURNS trigger
LANGUAGE plpgsql
AS $$ BEGIN
    INSERT INTO estados_pedido (pedido_id, usuario_id, estado, comentario)
    VALUES (NEW.id, NEW.usuario_id, 'pendiente', 'Pedido creado');
    RETURN NEW;
END $$;
SQL);
        }

        if (!Schema::hasTable('direcciones')) {
            Schema::create('direcciones', function (Blueprint $table) {
                $table->comment('Direcciones de envío guardadas por cliente (múltiples direcciones)');

                $table->id();
                $table->bigInteger('usuario_id');
                $table->string('alias', 50)->default('Casa');
                $table->string('nombre_receptor', 200);
                $table->string('provincia', 100);
                $table->string('distrito', 100);
                $table->string('corregimiento', 100);
                $table->text('direccion_exacta');
                $table->text('referencia')->nullable();
                $table->boolean('es_predeterminada')->default(false);
                $table->timestamp('eliminado_en')->nullable();
                $table->timestamp('creado_en')->useCurrent();
                $table->timestamp('actualizado_en')->useCurrent();

                $table->foreign('usuario_id', 'direcciones_usuario_id_fkey')->references('id')->on('usuarios')->onDelete('cascade');
            });

            DB::statement('CREATE INDEX idx_direcciones_usuario ON public.direcciones USING btree (usuario_id) WHERE (eliminado_en IS NULL)');

            DB::statement('DROP TRIGGER IF EXISTS trg_upd_direcciones ON public.direcciones');
            DB::statement('CREATE TRIGGER trg_upd_direcciones BEFORE UPDATE ON public.direcciones FOR EACH ROW EXECUTE FUNCTION public.actualizar_timestamp()');
        }

        if (!Schema::hasTable('pedidos')) {
            Schema::create('pedidos', function (Blueprint $table) {
                $table->comment('Pedidos realizados. El estado actual está en estados_pedido');

                $table->id();
                $table->bigInteger('usuario_id');
                $table->bigInteger('direccion_id')->nullable();
                $table->bigInteger('cupon_id')->nullable();
                $table->bigInteger('zona_envio_id')->nullable();
                $table->string('numero_pedido', 30);
                $table->string('metodo_pago', 30);
                $table->decimal('subtotal', 10, 2)->default(0);
                $table->decimal('descuento', 10, 2)->default(0);
                $table->decimal('costo_envio', 10, 2)->default(0);
                $table->decimal('itbms_monto', 10, 2)->default(0);
                $table->decimal('total', 10, 2)->default(0);
                $table->text('notas_cliente')->nullable();
                $table->text('notas_internas')->nullable();
                $table->string('comprobante_pago_ruta', 500)->nullable()->comment('Ruta relativa: storage/app/public/comprobantes/archivo.jpg');
                $table->timestamp('eliminado_en')->nullable();
                $table->timestamp('creado_en')->useCurrent();
                $table->timestamp('actualizado_en')->useCurrent();

                $table->unique(['numero_pedido'], 'pedidos_numero_pedido_key');
                $table->index(['usuario_id'], 'idx_pedidos_usuario');
                $table->index(['numero_pedido'], 'idx_pedidos_numero');
                $table->foreign('usuario_id', 'pedidos_usuario_id_fkey')->references('id')->on('usuarios');
                $table->foreign('direccion_id', 'pedidos_direccion_id_fkey')->references('id')->on('direcciones')->onDelete('set null');
            });

            DB::statement('CREATE INDEX idx_pedidos_creado ON public.pedidos USING btree (creado_en DESC)');

            DB::statement('ALTER TABLE public.pedidos ADD CONSTRAINT pedidos_metodo_pago_check CHECK ((metodo_pago)::text = ANY ((ARRAY[(\'stripe\'::character varying), (\'yappy\'::character varying), (\'transferencia\'::character varying), (\'contra_entrega\'::character varying)])::text[]))');

            DB::statement('DROP TRIGGER IF EXISTS trg_upd_pedidos ON public.pedidos');
            DB::statement('DROP TRIGGER IF EXISTS trg_numero_pedido ON public.pedidos');
            DB::statement('DROP TRIGGER IF EXISTS trg_estado_inicial_pedido ON public.pedidos');
            DB::statement('CREATE TRIGGER trg_upd_pedidos BEFORE UPDATE ON public.pedidos FOR EACH ROW EXECUTE FUNCTION public.actualizar_timestamp()');
            DB::statement('CREATE TRIGGER trg_numero_pedido BEFORE INSERT ON public.pedidos FOR EACH ROW WHEN (((new.numero_pedido IS NULL) OR ((new.numero_pedido)::text = \'\'::text))) EXECUTE FUNCTION public.generar_numero_pedido()');
            DB::statement('CREATE TRIGGER trg_estado_inicial_pedido AFTER INSERT ON public.pedidos FOR EACH ROW EXECUTE FUNCTION public.registrar_estado_inicial_pedido()');
        }

        if (!Schema::hasTable('items_pedido')) {
            Schema::create('items_pedido', function (Blueprint $table) {
                $table->comment('Productos incluidos en cada pedido con precio congelado');

                $table->id();
                $table->bigInteger('pedido_id');
                $table->bigInteger('producto_id');
                $table->bigInteger('variante_producto_id')->nullable();
                $table->integer('cantidad');
                $table->decimal('precio_unitario', 10, 2);
                $table->decimal('subtotal', 10, 2)->default(0);
                $table->timestamp('creado_en')->useCurrent();

                $table->index(['pedido_id'], 'idx_items_pedido_pedido');
                $table->index(['producto_id'], 'idx_items_pedido_producto');
                $table->foreign('pedido_id', 'items_pedido_pedido_id_fkey')->references('id')->on('pedidos')->onDelete('cascade');
                $table->foreign('producto_id', 'items_pedido_producto_id_fkey')->references('id')->on('productos');
                $table->foreign('variante_producto_id', 'items_pedido_variante_producto_id_fkey')->references('id')->on('variantes_producto')->onDelete('set null');
            });

            DB::statement('ALTER TABLE public.items_pedido ADD CONSTRAINT items_pedido_cantidad_check CHECK (cantidad > 0)');
            DB::statement('ALTER TABLE public.items_pedido ADD CONSTRAINT items_pedido_precio_unitario_check CHECK (precio_unitario >= 0)');
        }

        if (!Schema::hasTable('estados_pedido')) {
            Schema::create('estados_pedido', function (Blueprint $table) {
                $table->comment('Historial completo de estados del pedido (tabla aparte)');

                $table->id();
                $table->bigInteger('pedido_id');
                $table->bigInteger('usuario_id')->nullable();
                $table->string('estado', 40)->comment("pendiente \n pago_confirmado \n pago_rechazado \n en_preparacion \n listo_para_envio \n enviado \n entregado \n cancelado \n devolucion_solicitada \n devolucion_aprobada \n devolucion_rechazada");
                $table->text('comentario')->nullable();
                $table->timestamp('creado_en')->useCurrent();

                $table->index(['pedido_id'], 'idx_estados_pedido_pedido');
                $table->index(['estado'], 'idx_estados_pedido_estado');
                $table->foreign('pedido_id', 'estados_pedido_pedido_id_fkey')->references('id')->on('pedidos')->onDelete('cascade');
                $table->foreign('usuario_id', 'estados_pedido_usuario_id_fkey')->references('id')->on('usuarios')->onDelete('set null');
            });

            DB::statement('ALTER TABLE public.estados_pedido ADD CONSTRAINT estados_pedido_estado_check CHECK ((estado)::text = ANY ((ARRAY[(\'pendiente\'::character varying), (\'pago_confirmado\'::character varying), (\'pago_rechazado\'::character varying), (\'en_preparacion\'::character varying), (\'listo_para_envio\'::character varying), (\'enviado\'::character varying), (\'entregado\'::character varying), (\'cancelado\'::character varying), (\'devolucion_solicitada\'::character varying), (\'devolucion_aprobada\'::character varying), (\'devolucion_rechazada\'::character varying)])::text[]))');
        }

        if (!Schema::hasTable('envios_pedido')) {
            Schema::create('envios_pedido', function (Blueprint $table) {
                $table->comment('Datos de envío: mensajería y rastreo');

                $table->id();
                $table->bigInteger('pedido_id');
                $table->string('empresa_mensajeria', 150)->nullable();
                $table->string('numero_guia', 150)->nullable();
                $table->string('url_rastreo', 500)->nullable();
                $table->timestamp('fecha_estimada_entrega')->nullable();
                $table->timestamp('fecha_entrega_real')->nullable();
                $table->timestamp('creado_en')->useCurrent();
                $table->timestamp('actualizado_en')->useCurrent();

                $table->unique(['pedido_id'], 'envios_pedido_pedido_id_key');
                $table->foreign('pedido_id', 'envios_pedido_pedido_id_fkey')->references('id')->on('pedidos')->onDelete('cascade');
            });

            DB::statement('DROP TRIGGER IF EXISTS trg_upd_envios_pedido ON public.envios_pedido');
            DB::statement('CREATE TRIGGER trg_upd_envios_pedido BEFORE UPDATE ON public.envios_pedido FOR EACH ROW EXECUTE FUNCTION public.actualizar_timestamp()');
        }

        if (!Schema::hasTable('devoluciones')) {
            Schema::create('devoluciones', function (Blueprint $table) {
                $table->comment('Solicitudes de devolución iniciadas por el cliente');

                $table->id();
                $table->bigInteger('pedido_id');
                $table->bigInteger('usuario_id');
                $table->string('motivo', 100);
                $table->text('descripcion');
                $table->string('foto_evidencia_ruta', 500)->nullable()->comment('Ruta relativa: storage/app/public/devoluciones/foto.jpg');
                $table->string('estado', 30)->default('pendiente');
                $table->text('comentario_admin')->nullable();
                $table->timestamp('aprobado_en')->nullable();
                $table->timestamp('creado_en')->useCurrent();
                $table->timestamp('actualizado_en')->useCurrent();

                $table->foreign('pedido_id', 'devoluciones_pedido_id_fkey')->references('id')->on('pedidos');
                $table->foreign('usuario_id', 'devoluciones_usuario_id_fkey')->references('id')->on('usuarios');
            });

            DB::statement('ALTER TABLE public.devoluciones ADD CONSTRAINT devoluciones_estado_check CHECK ((estado)::text = ANY ((ARRAY[(\'pendiente\'::character varying), (\'aprobada\'::character varying), (\'rechazada\'::character varying)])::text[]))');

            DB::statement('DROP TRIGGER IF EXISTS trg_upd_devoluciones ON public.devoluciones');
            DB::statement('CREATE TRIGGER trg_upd_devoluciones BEFORE UPDATE ON public.devoluciones FOR EACH ROW EXECUTE FUNCTION public.actualizar_timestamp()');
        }
    }

    public function down(): void
    {
        DB::statement('DROP TRIGGER IF EXISTS trg_estado_inicial_pedido ON public.pedidos');
        DB::statement('DROP TRIGGER IF EXISTS trg_numero_pedido ON public.pedidos');
        DB::statement('DROP TRIGGER IF EXISTS trg_upd_pedidos ON public.pedidos');
        DB::statement('DROP TRIGGER IF EXISTS trg_upd_devoluciones ON public.devoluciones');
        DB::statement('DROP TRIGGER IF EXISTS trg_upd_envios_pedido ON public.envios_pedido');
        DB::statement('DROP TRIGGER IF EXISTS trg_upd_direcciones ON public.direcciones');

        DB::unprepared('DROP FUNCTION IF EXISTS public.generar_numero_pedido()');
        DB::unprepared('DROP FUNCTION IF EXISTS public.registrar_estado_inicial_pedido()');

        Schema::dropIfExists('devoluciones');
        Schema::dropIfExists('envios_pedido');
        Schema::dropIfExists('estados_pedido');
        Schema::dropIfExists('items_pedido');
        Schema::dropIfExists('pedidos');
        Schema::dropIfExists('direcciones');
    }
};