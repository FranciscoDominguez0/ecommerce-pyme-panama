<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('courier_sucursales', function (Blueprint $table) {
            $table->id();
            $table->string('zona');
            $table->string('courier');
            $table->string('sucursal');
            $table->text('direccion');
            $table->decimal('tarifa_uno_hasta_7lb', 8, 2)->nullable();
            $table->string('verificacion')->nullable();
            $table->text('observaciones')->nullable();
            $table->boolean('activo')->default(true);
            $table->string('telefono')->nullable();
            $table->string('fuente_url', 500)->nullable();
            $table->string('tipo_punto')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courier_sucursales');
    }
};
