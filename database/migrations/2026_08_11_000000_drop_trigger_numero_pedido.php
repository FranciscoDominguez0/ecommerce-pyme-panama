<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Elimina el trigger y la función DB que generaban "numero_pedido".
 *
 * MOTIVO: existían DOS mecanismos en conflicto para generar numero_pedido:
 *  1. Un trigger DB (trg_numero_pedido → generar_numero_pedido()) que generaba el
 *     formato "P-YYYY-000001" al insertar.
 *  2. El código PHP en PedidoService que SOBRESCRIBÍA ese valor con "#PM-XXXXXX".
 *
 * DECISIÓN: se conserva el formato corto "#PM-XXXXXX" como ÚNICA fuente de verdad,
 * generado en PHP por PedidoService::generarNumeroPedido() (correlativo atómico en
 * "configuracion" con bloqueo de fila — el mismo patrón seguro de facturas.numero).
 * Este trigger era redundante (su valor siempre se descartaba) y confuso.
 *
 * El trigger de ESTADO inicial (trg_estado_inicial_pedido) NO se toca: sigue siendo
 * la fuente del primer registro en estados_pedido.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('pedidos')) {
            return;
        }

        DB::statement('DROP TRIGGER IF EXISTS trg_numero_pedido ON public.pedidos');
        DB::unprepared('DROP FUNCTION IF EXISTS public.generar_numero_pedido()');
    }

    public function down(): void
    {
        // Recrear la función y el trigger originales (seguridad de rollback).
        if (!Schema::hasTable('pedidos')) {
            return;
        }

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

        DB::statement('DROP TRIGGER IF EXISTS trg_numero_pedido ON public.pedidos');
        DB::statement('CREATE TRIGGER trg_numero_pedido BEFORE INSERT ON public.pedidos FOR EACH ROW WHEN (((new.numero_pedido IS NULL) OR ((new.numero_pedido)::text = \'\'::text))) EXECUTE FUNCTION public.generar_numero_pedido()');
    }
};
