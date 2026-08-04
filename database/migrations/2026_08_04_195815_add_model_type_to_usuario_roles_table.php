<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('usuario_roles', function (Blueprint $table) {
            $table->string('model_type')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('usuario_roles', function (Blueprint $table) {
            $table->dropColumn('model_type');
        });
    }
};