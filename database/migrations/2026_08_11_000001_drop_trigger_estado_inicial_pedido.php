<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Elimina el trigger y la función DB que insertaban el ESTADO INICIAL "pendiente".
 *
 * MOTIVO: al crear un pedido quedaban DOS filas "pendiente" en estados_pedido:
 *  1. El trigger DB (trg_estado_inicial_pedido → registrar_estado_inicial_pedido()).
 *  2. El código PHP de PedidoService::crearDesdeCarrito (via cambiarEstado).
 *
 * DECISIÓN: las transiciones de estado se gestionan TODAS en PHP a través de
 * PedidoService::cambiarEstado (incluido el estado inicial). Se elimina el trigger
 * para tener UNA sola fuente de verdad y exactamente 1 fila "pendiente" al crear.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('pedidos')) {
            return;
        }

        DB::statement('DROP TRIGGER IF EXISTS trg_estado_inicial_pedido ON public.pedidos');
        DB::unprepared('DROP FUNCTION IF EXISTS public.registrar_estado_inicial_pedido()');
    }

    public function down(): void
    {
        // Recrear la función y el trigger originales (seguridad de rollback).
        if (!Schema::hasTable('pedidos')) {
            return;
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

        DB::statement('DROP TRIGGER IF EXISTS trg_estado_inicial_pedido ON public.pedidos');
        DB::statement('CREATE TRIGGER trg_estado_inicial_pedido AFTER INSERT ON public.pedidos FOR EACH ROW EXECUTE FUNCTION public.registrar_estado_inicial_pedido()');
    }
};
