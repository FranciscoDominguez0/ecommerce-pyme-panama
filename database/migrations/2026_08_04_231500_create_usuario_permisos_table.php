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
        if (!Schema::hasTable('usuario_permisos')) {
            Schema::create('usuario_permisos', function (Blueprint $table) {
                $table->unsignedBigInteger('permiso_id');
                $table->string('model_type');
                $table->unsignedBigInteger('usuario_id');

                $table->primary(['permiso_id', 'usuario_id', 'model_type']);
                $table->index(['usuario_id', 'model_type']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usuario_permisos');
    }
};
