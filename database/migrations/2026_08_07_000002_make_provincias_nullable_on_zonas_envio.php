<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('zonas_envio')) {
            Schema::table('zonas_envio', function (Blueprint $table) {
                if (Schema::hasColumn('zonas_envio', 'provincias')) {
                    $table->text('provincias')->nullable()->change();
                }
                if (Schema::hasColumn('zonas_envio', 'provincia')) {
                    $table->string('provincia')->nullable()->change();
                }
            });
        }
    }

    public function down(): void
    {
        //
    }
};
