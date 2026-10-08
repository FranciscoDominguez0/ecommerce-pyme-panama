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
        Schema::table('productos', function (Blueprint $table) {
            $table->json('especificaciones')->nullable()->after('descripcion_corta');
            $table->decimal('peso', 8, 3)->nullable()->comment('Peso en kg')->after('especificaciones');
            $table->decimal('dimension_largo', 8, 2)->nullable()->comment('Largo en cm')->after('peso');
            $table->decimal('dimension_ancho', 8, 2)->nullable()->comment('Ancho en cm')->after('dimension_largo');
            $table->decimal('dimension_alto', 8, 2)->nullable()->comment('Alto en cm')->after('dimension_ancho');
            $table->json('garantia_info')->nullable()->after('dimension_alto');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->dropColumn([
                'especificaciones',
                'peso',
                'dimension_largo',
                'dimension_ancho',
                'dimension_alto',
                'garantia_info'
            ]);
        });
    }
};
