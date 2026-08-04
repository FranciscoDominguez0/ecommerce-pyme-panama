<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->string('name', 125)->nullable()->after('nombre');
            $table->string('guard_name', 125)->default('web')->after('name');
        });

        DB::statement('UPDATE roles SET name = nombre');

        Schema::table('roles', function (Blueprint $table) {
            $table->string('name', 125)->nullable(false)->change();
            $table->unique(['name', 'guard_name']);
        });

        Schema::table('permisos', function (Blueprint $table) {
            $table->string('name', 150)->nullable()->after('nombre');
            $table->string('guard_name', 125)->default('web')->after('name');
        });

        DB::statement('UPDATE permisos SET name = nombre');

        Schema::table('permisos', function (Blueprint $table) {
            $table->string('name', 150)->nullable(false)->change();
            $table->unique(['name', 'guard_name']);
        });
    }

    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropUnique(['name', 'guard_name']);
            $table->dropColumn(['name', 'guard_name']);
        });

        Schema::table('permisos', function (Blueprint $table) {
            $table->dropUnique(['name', 'guard_name']);
            $table->dropColumn(['name', 'guard_name']);
        });
    }
};