<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('brands', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->string('slug', 255);
            $table->binary('image')->nullable();
            $table->string('image_mime', 50)->nullable();
            $table->string('image_path', 255)->nullable();
            $table->boolean('verified')->default(false);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->unique(['slug'], 'brands_slug_unique');
        });

        Schema::create('categorias', function (Blueprint $table) {
            $table->comment('Categorías y subcategorías (padre_id self-reference)');

            $table->id();
            $table->bigInteger('padre_id')->nullable();
            $table->string('nombre', 150);
            $table->string('slug', 200);
            $table->text('descripcion')->nullable();
            $table->string('imagen_ruta', 500)->nullable();
            $table->boolean('activo')->default(true);
            $table->integer('orden_visualizacion')->default(0);
            $table->timestamp('eliminado_en')->nullable();
            $table->timestamp('creado_en')->useCurrent();
            $table->timestamp('actualizado_en')->useCurrent();

            $table->unique(['slug'], 'categorias_slug_key');
            $table->index(['slug'], 'idx_categorias_slug');
            $table->index(['padre_id'], 'idx_categorias_padre');
            $table->foreign('padre_id', 'categorias_padre_id_fkey')->references('id')->on('categorias')->onDelete('set null');
        });

        DB::statement('CREATE INDEX idx_categorias_activo ON public.categorias USING btree (activo) WHERE (eliminado_en IS NULL)');

        DB::statement('DROP TRIGGER IF EXISTS trg_upd_categorias ON public.categorias');
        DB::statement('CREATE TRIGGER trg_upd_categorias BEFORE UPDATE ON public.categorias FOR EACH ROW EXECUTE FUNCTION public.actualizar_timestamp()');

        Schema::create('productos', function (Blueprint $table) {
            $table->comment('Catálogo de productos. Imágenes en disco, ruta en BD');

            $table->id();
            $table->bigInteger('categoria_id')->nullable();
            $table->string('nombre', 255);
            $table->string('slug', 300);
            $table->text('descripcion')->nullable();
            $table->string('descripcion_corta', 500)->nullable();
            $table->string('sku', 100);
            $table->decimal('precio', 10, 2);
            $table->decimal('precio_oferta', 10, 2)->nullable()->comment('Si NULL, no hay oferta activa');
            $table->boolean('oferta_activa')->default(false)->comment('Activado/desactivado por scheduler según oferta_inicio_en/oferta_fin_en');
            $table->timestamp('oferta_inicio_en')->nullable();
            $table->timestamp('oferta_fin_en')->nullable();
            $table->integer('stock')->default(0);
            $table->integer('stock_minimo')->default(5);
            $table->boolean('destacado')->default(false);
            $table->boolean('activo')->default(true);
            $table->boolean('aplica_itbms')->default(true);
            $table->timestamp('eliminado_en')->nullable();
            $table->timestamp('creado_en')->useCurrent();
            $table->timestamp('actualizado_en')->useCurrent();
            $table->string('marca', 100)->nullable();
            $table->string('modelo', 100)->nullable();
            $table->string('marca_logo', 255)->nullable();
            $table->bigInteger('brand_id')->nullable();

            $table->unique(['sku'], 'productos_sku_key');
            $table->unique(['slug'], 'productos_slug_key');
            $table->index(['sku'], 'idx_productos_sku');
            $table->index(['slug'], 'idx_productos_slug');
            $table->index(['categoria_id'], 'idx_productos_categoria');
            $table->foreign('categoria_id', 'productos_categoria_id_fkey')->references('id')->on('categorias')->onDelete('set null');
            $table->foreign('brand_id', 'productos_brand_id_foreign')->references('id')->on('brands')->onDelete('set null');
        });

        DB::statement('CREATE INDEX idx_productos_activo ON public.productos USING btree (activo) WHERE (eliminado_en IS NULL)');
        DB::statement('CREATE INDEX idx_productos_destacado ON public.productos USING btree (destacado) WHERE (activo = true)');
        DB::statement('CREATE INDEX idx_productos_oferta ON public.productos USING btree (oferta_activa) WHERE (activo = true)');
        DB::statement('CREATE INDEX idx_productos_stock_bajo ON public.productos USING btree (stock) WHERE ((stock <= stock_minimo) AND (eliminado_en IS NULL))');

        DB::statement('ALTER TABLE public.productos ADD CONSTRAINT productos_precio_check CHECK (precio >= 0)');
        DB::statement('ALTER TABLE public.productos ADD CONSTRAINT productos_precio_oferta_check CHECK (precio_oferta >= 0)');
        DB::statement('ALTER TABLE public.productos ADD CONSTRAINT precio_oferta_menor CHECK ((precio_oferta IS NULL) OR (precio_oferta < precio))');
        DB::statement('ALTER TABLE public.productos ADD CONSTRAINT productos_stock_check CHECK (stock >= 0)');
        DB::statement('ALTER TABLE public.productos ADD CONSTRAINT productos_stock_minimo_check CHECK (stock_minimo >= 0)');

        DB::statement('DROP TRIGGER IF EXISTS trg_upd_productos ON public.productos');
        DB::statement('CREATE TRIGGER trg_upd_productos BEFORE UPDATE ON public.productos FOR EACH ROW EXECUTE FUNCTION public.actualizar_timestamp()');

        Schema::create('imagenes_producto', function (Blueprint $table) {
            $table->comment('Rutas de imágenes del producto (storage/app/public/products/)');

            $table->id();
            $table->bigInteger('producto_id');
            $table->string('ruta', 500)->comment('Ruta relativa: storage/app/public/products/imagen.jpg');
            $table->boolean('es_principal')->default(false);
            $table->integer('orden')->default(0);
            $table->timestamp('creado_en')->useCurrent();

            $table->index(['producto_id'], 'idx_imagenes_producto');
            $table->index(['producto_id', 'es_principal'], 'idx_imagenes_principal');
            $table->foreign('producto_id', 'imagenes_producto_producto_id_fkey')->references('id')->on('productos')->onDelete('cascade');
        });

        Schema::create('tipos_variante', function (Blueprint $table) {
            $table->comment('Tipos de variante: Color, Talla, Capacidad, etc.');

            $table->id();
            $table->string('nombre', 100);
            $table->timestamp('creado_en')->useCurrent();

            $table->unique(['nombre'], 'tipos_variante_nombre_key');
        });

        Schema::create('opciones_variante', function (Blueprint $table) {
            $table->comment('Valores por tipo: Rojo, XL, 128GB, etc.');

            $table->id();
            $table->bigInteger('tipo_variante_id');
            $table->string('valor', 100);
            $table->string('valor_hex', 7)->nullable();
            $table->timestamp('creado_en')->useCurrent();

            $table->unique(['tipo_variante_id', 'valor'], 'opciones_variante_tipo_variante_id_valor_key');
            $table->foreign('tipo_variante_id', 'opciones_variante_tipo_variante_id_fkey')->references('id')->on('tipos_variante')->onDelete('cascade');
        });

        Schema::create('variantes_producto', function (Blueprint $table) {
            $table->comment('Combinación de opciones con stock y precio propio');

            $table->id();
            $table->bigInteger('producto_id');
            $table->string('sku', 100);
            $table->decimal('precio', 10, 2)->nullable();
            $table->integer('stock')->default(0);
            $table->string('imagen_ruta', 500)->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamp('creado_en')->useCurrent();
            $table->timestamp('actualizado_en')->useCurrent();

            $table->unique(['sku'], 'variantes_producto_sku_key');
            $table->index(['sku'], 'idx_variantes_sku');
            $table->index(['producto_id'], 'idx_variantes_producto');
            $table->foreign('producto_id', 'variantes_producto_producto_id_fkey')->references('id')->on('productos')->onDelete('cascade');
        });

        DB::statement('ALTER TABLE public.variantes_producto ADD CONSTRAINT variantes_producto_precio_check CHECK (precio >= 0)');
        DB::statement('ALTER TABLE public.variantes_producto ADD CONSTRAINT variantes_producto_stock_check CHECK (stock >= 0)');

        DB::statement('DROP TRIGGER IF EXISTS trg_upd_variantes_producto ON public.variantes_producto');
        DB::statement('CREATE TRIGGER trg_upd_variantes_producto BEFORE UPDATE ON public.variantes_producto FOR EACH ROW EXECUTE FUNCTION public.actualizar_timestamp()');

        Schema::create('variante_opciones', function (Blueprint $table) {
            $table->comment('Pivot: qué opciones conforman cada variante');

            $table->bigInteger('variante_producto_id');
            $table->bigInteger('opcion_variante_id');

            $table->primary(['variante_producto_id', 'opcion_variante_id']);
            $table->foreign('variante_producto_id', 'variante_opciones_variante_producto_id_fkey')->references('id')->on('variantes_producto')->onDelete('cascade');
            $table->foreign('opcion_variante_id', 'variante_opciones_opcion_variante_id_fkey')->references('id')->on('opciones_variante')->onDelete('cascade');
        });

        Schema::create('producto_del_mes', function (Blueprint $table) {
            $table->comment('Producto destacado en home (solo 1 activo)');

            $table->id();
            $table->bigInteger('producto_id');
            $table->text('descripcion_mes')->nullable();
            $table->string('imagen_banner_ruta', 500)->nullable();
            $table->decimal('descuento_especial', 5, 2)->nullable();
            $table->timestamp('inicio_en');
            $table->timestamp('fin_en');
            $table->boolean('activo')->default(true);
            $table->timestamp('creado_en')->useCurrent();

            $table->foreign('producto_id', 'producto_del_mes_producto_id_fkey')->references('id')->on('productos')->onDelete('cascade');
        });

        DB::statement('ALTER TABLE public.producto_del_mes ADD CONSTRAINT producto_del_mes_descuento_especial_check CHECK ((descuento_especial >= 0) AND (descuento_especial <= 100))');
    }

    public function down(): void
    {
        DB::statement('DROP TRIGGER IF EXISTS trg_upd_categorias ON public.categorias');
        DB::statement('DROP TRIGGER IF EXISTS trg_upd_productos ON public.productos');
        DB::statement('DROP TRIGGER IF EXISTS trg_upd_variantes_producto ON public.variantes_producto');

        Schema::dropIfExists('producto_del_mes');
        Schema::dropIfExists('variante_opciones');
        Schema::dropIfExists('opciones_variante');
        Schema::dropIfExists('variantes_producto');
        Schema::dropIfExists('tipos_variante');
        Schema::dropIfExists('imagenes_producto');
        Schema::dropIfExists('productos');
        Schema::dropIfExists('categorias');
        Schema::dropIfExists('brands');
    }
};
