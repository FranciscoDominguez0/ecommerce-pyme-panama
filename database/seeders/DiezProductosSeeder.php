<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Categoria;
use App\Models\Producto;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DiezProductosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categorias = Categoria::all();
        $brands = Brand::all();

        if ($categorias->isEmpty() || $brands->isEmpty()) {
            $this->command->warn('Se requieren categorías y marcas en la base de datos.');
            return;
        }

        for ($i = 1; $i <= 10; $i++) {
            $categoria = $categorias->random();
            $brand = $brands->random();
            
            $nombre = "Nuevo Producto Especial " . Str::random(5) . " de " . $brand->name;

            Producto::factory()->create([
                'categoria_id' => $categoria->id,
                'brand_id' => $brand->id,
                'nombre' => $nombre,
                'slug' => Str::slug($nombre) . '-' . uniqid(),
                'marca' => $brand->name,
                'precio' => rand(10, 900) + 0.99,
                'stock' => rand(10, 100),
                'destacado' => true,
            ]);
        }

        $this->command->info('¡10 productos generados y vinculados correctamente!');
    }
}
