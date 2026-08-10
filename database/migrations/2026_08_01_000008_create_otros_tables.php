<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('movimientos_inventario')) {
            Schema::create('movimientos_inventario', function (Blueprint $table) {
                $table->id();
                $table->bigInteger('producto_id');
                $table->bigInteger('variante_producto_id')->nullable();
                $table->bigInteger('usuario_id')->nullable();
                $table->bigInteger('pedido_id')->nullable();
                $table->string('tipo', 20);
                $table->integer('cantidad');
                $table->integer('stock_antes');
                $table->integer('stock_despues');
                $table->text('motivo');
                $table->string('proveedor', 200)->nullable();
                $table->string('factura_proveedor', 100)->nullable();
                $table->text('notas')->nullable();
                $table->timestamp('creado_en')->useCurrent();
        });
        }

        if (!Schema::hasTable('logs_auditoria')) {
            Schema::create('logs_auditoria', function (Blueprint $table) {
                $table->id();
                $table->bigInteger('usuario_id')->nullable();
                $table->string('modulo', 100);
                $table->string('accion', 100);
                $table->text('descripcion')->nullable();
                $table->text('valor_anterior')->nullable();
                $table->text('valor_nuevo')->nullable();
                $table->string('ip', 45)->nullable();
                $table->string('agente_usuario', 500)->nullable();
                $table->timestamp('creado_en')->useCurrent();
        });
        }

        if (!Schema::hasTable('configuracion')) {
            Schema::create('configuracion', function (Blueprint $table) {
                $table->id();
                $table->string('clave', 150);
                $table->text('valor')->nullable();
                $table->string('grupo', 50);
                $table->text('descripcion')->nullable();
                $table->timestamp('actualizado_en')->useCurrent();
                $table->unique(['clave']);
        });
        }

        $fnFactura = DB::select("SELECT 1 FROM pg_proc WHERE proname = 'generar_numero_factura'");
        if (empty($fnFactura)) {
            DB::unprepared(<<<'SQL'
CREATE FUNCTION public.generar_numero_factura()
RETURNS trigger
LANGUAGE plpgsql
AS $$
DECLARE
    v_prefijo   VARCHAR;
    v_anio      VARCHAR;
    v_correlativo BIGINT;
BEGIN
    SELECT valor INTO v_prefijo FROM configuracion WHERE clave = 'factura_prefijo';
    v_prefijo   := COALESCE(v_prefijo, 'F');
    v_anio      := TO_CHAR(NOW(), 'YYYY');
    UPDATE configuracion
    SET valor = (COALESCE(valor::BIGINT, 0) + 1)::TEXT,
        actualizado_en = NOW()
    WHERE clave = 'factura_correlativo'
    RETURNING valor::BIGINT INTO v_correlativo;
    NEW.numero := v_prefijo || '-' || v_anio || '-' || LPAD(v_correlativo::TEXT, 4, '0');
    RETURN NEW;
END;
$$;
SQL);
        }

        if (Schema::hasTable('facturas')) {
            DB::statement('DROP TRIGGER IF EXISTS trg_numero_factura ON public.facturas');
            DB::statement('CREATE TRIGGER trg_numero_factura BEFORE INSERT ON public.facturas FOR EACH ROW WHEN (((new.numero IS NULL) OR ((new.numero)::text = \'\'::text))) EXECUTE FUNCTION public.generar_numero_factura()');
        }
    }

    public function down(): void
    {
            DB::statement('DROP TRIGGER IF EXISTS trg_numero_factura ON public.facturas');
            DB::unprepared('DROP FUNCTION IF EXISTS public.generar_numero_factura()');

            Schema::dropIfExists('configuracion');
            Schema::dropIfExists('logs_auditoria');
            Schema::dropIfExists('movimientos_inventario');
    }
};
