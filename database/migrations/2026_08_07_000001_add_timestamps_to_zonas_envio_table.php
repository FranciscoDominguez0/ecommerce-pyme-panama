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
                if (!Schema::hasColumn('zonas_envio', 'created_at')) {
                    $table->timestamp('created_at')->nullable();
                }
                if (!Schema::hasColumn('zonas_envio', 'updated_at')) {
                    $table->timestamp('updated_at')->nullable();
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('zonas_envio')) {
            Schema::table('zonas_envio', function (Blueprint $table) {
                if (Schema::hasColumn('zonas_envio', 'created_at')) {
                    $table->dropColumn('created_at');
                }
                if (Schema::hasColumn('zonas_envio', 'updated_at')) {
                    $table->dropColumn('updated_at');
                }
            });
        }
    }
};
