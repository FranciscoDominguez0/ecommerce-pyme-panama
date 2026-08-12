<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Usuario;

class RolesPermisosSeeder extends Seeder
{
    public function run(): void
    {
        // Los roles y permisos ya existen en la BD (vienen de RolesSeeder),
        // así que aquí solo creamos el primer usuario admin y le asignamos el rol.
        //
        // Usuario admin GENÉRICO de arranque. Cambia estas credenciales en
        // producción (o crea tu propio usuario desde el panel).

        $admin = Usuario::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'nombre' => 'Admin',
                'apellido' => 'Principal',
                'password_hash' => bcrypt('Admin1234!'),
            ]
        );

        $admin->assignRole('super_admin');
    }
}