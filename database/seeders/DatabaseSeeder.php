<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            RolesSeeder::class,           // 1. Roles básicos (super_admin, admin, cliente)
            RolesPermisosSeeder::class,   // 2. Permisos y el usuario admin genérico
            BrandSeeder::class,           // 3. Marcas de productos
            CategoriaSeeder::class,       // 4. Categorías principales y subcategorías
            AtributosVarianteSeeder::class, // 5. Atributos y opciones para variantes
            CourierSucursalesSeeder::class, // 6. Sucursales de Courier (Fletes Chavale, etc.)
            ZonaEnvioSeeder::class,         // 7. Zonas de envío (Panamá, etc.) y promociones de flete
            CatalogoFijoSeeder::class,    // 8. Productos fijos (25 productos reales con especificaciones y garantías)
        ]);
    }
}
