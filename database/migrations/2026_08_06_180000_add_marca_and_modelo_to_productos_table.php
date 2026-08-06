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
            if (!Schema::hasColumn('productos', 'marca')) {
                $table->string('marca', 100)->nullable()->after('sku');
            }
            if (!Schema::hasColumn('productos', 'modelo')) {
                $table->string('modelo', 100)->nullable()->after('marca');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            if (Schema::hasColumn('productos', 'marca')) {
                $table->dropColumn('marca');
            }
            if (Schema::hasColumn('productos', 'modelo')) {
                $table->dropColumn('modelo');
            }
        });
    }
};
