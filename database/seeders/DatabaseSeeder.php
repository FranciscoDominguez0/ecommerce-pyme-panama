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
            ZonaEnvioSeeder::class,       // 6. Zonas de envío (Panamá)
        ]);
    }
}
