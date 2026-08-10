<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('facturas')) {
            Schema::create('facturas', function (Blueprint $table) {
                $table->comment('Facturas generadas automáticamente al aprobar pago. PDF en disco');

                $table->id();
                $table->bigInteger('pedido_id');
                $table->bigInteger('usuario_id');
                $table->string('numero', 30);
                $table->string('metodo_pago', 30)->nullable();
                $table->string('referencia_pago_externo', 255)->nullable();
                $table->decimal('subtotal', 10, 2)->default(0);
                $table->decimal('descuento', 10, 2)->default(0);
                $table->decimal('costo_envio', 10, 2)->default(0);
                $table->decimal('itbms_tasa', 5, 2)->default(7.00);
                $table->decimal('itbms_monto', 10, 2)->default(0);
                $table->decimal('total', 10, 2)->default(0);
                $table->string('estado', 30)->default('emitida');
                $table->string('pdf_ruta', 500)->nullable()->comment('Ruta relativa: storage/app/public/facturas/F-2024-0001.pdf');
                $table->timestamp('emitida_en')->useCurrent();
                $table->timestamp('creado_en')->useCurrent();
                $table->timestamp('actualizado_en')->useCurrent();

                $table->unique(['numero'], 'facturas_numero_key');
                $table->unique(['pedido_id'], 'facturas_pedido_id_key');
                $table->index(['pedido_id'], 'idx_facturas_pedido');
                $table->index(['usuario_id'], 'idx_facturas_usuario');
                $table->index(['numero'], 'idx_facturas_numero');
                $table->foreign('pedido_id', 'facturas_pedido_id_fkey')->references('id')->on('pedidos');
                $table->foreign('usuario_id', 'facturas_usuario_id_fkey')->references('id')->on('usuarios');
            });

            DB::statement('CREATE INDEX idx_facturas_emitida ON public.facturas USING btree (emitida_en DESC)');

            DB::statement('ALTER TABLE public.facturas ADD CONSTRAINT facturas_estado_check CHECK ((estado)::text = ANY ((ARRAY[(\'emitida\'::character varying), (\'anulada\'::character varying)])::text[]))');

            DB::statement('DROP TRIGGER IF EXISTS trg_upd_facturas ON public.facturas');
            DB::statement('CREATE TRIGGER trg_upd_facturas BEFORE UPDATE ON public.facturas FOR EACH ROW EXECUTE FUNCTION public.actualizar_timestamp()');
        }

        if (!Schema::hasTable('reenvios_factura')) {
            Schema::create('reenvios_factura', function (Blueprint $table) {
                $table->comment('Registro de cada vez que se reenvía una factura por email');

                $table->id();
                $table->bigInteger('factura_id');
                $table->bigInteger('usuario_id')->nullable();
                $table->string('email_destino', 255);
                $table->text('mensaje_personalizado')->nullable();
                $table->timestamp('enviado_en')->useCurrent();

                $table->foreign('factura_id', 'reenvios_factura_factura_id_fkey')->references('id')->on('facturas');
                $table->foreign('usuario_id', 'reenvios_factura_usuario_id_fkey')->references('id')->on('usuarios')->onDelete('set null');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('reenvios_factura');
        Schema::dropIfExists('facturas');
    }
};