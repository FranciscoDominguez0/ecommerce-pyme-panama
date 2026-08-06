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
        Schema::create('brands', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->binary('image')->nullable();
            $table->string('image_mime', 50)->nullable();
            $table->string('image_path')->nullable();
            $table->boolean('verified')->default(false);
            $table->boolean('is_suggested')->default(false);
            $table->timestamps();
        });

        // Agregar brand_id a productos si no existe
        if (Schema::hasTable('productos') && !Schema::hasColumn('productos', 'brand_id')) {
            Schema::table('productos', function (Blueprint $table) {
                $table->foreignId('brand_id')->nullable()->after('categoria_id')->constrained('brands')->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('productos') && Schema::hasColumn('productos', 'brand_id')) {
            Schema::table('productos', function (Blueprint $table) {
                $table->dropConstrainedForeignId('brand_id');
            });
        }

        Schema::dropIfExists('brands');
    }
};
