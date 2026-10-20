<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Elimina el check constraint estático para permitir la extensión abierta (OCP)
     * de nuevos métodos de pago sin modificar el esquema de base de datos.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE public.pedidos DROP CONSTRAINT IF EXISTS pedidos_metodo_pago_check');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE public.pedidos ADD CONSTRAINT pedidos_metodo_pago_check CHECK ((metodo_pago)::text = ANY ((ARRAY['stripe'::character varying, 'yappy'::character varying, 'transferencia'::character varying, 'contra_entrega'::character varying])::text[]))");
        }
    }
};
