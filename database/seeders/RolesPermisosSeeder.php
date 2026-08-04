<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Usuario;

class RolesPermisosSeeder extends Seeder
{
    public function run(): void
    {
        // Los roles y permisos ya existen en la BD (vienen de tu script .sql),
        // así que aquí solo creamos el primer usuario admin y le asignamos el rol.

        $admin = Usuario::firstOrCreate(
            ['email' => 'dominguezf225@gmail.com'],
            [
                'nombre' => 'Admin',
                'apellido' => 'Principal',
                'password_hash' => bcrypt('4e369CBEAD'),
            ]
        );

        $admin->assignRole('super_admin');
    }
}