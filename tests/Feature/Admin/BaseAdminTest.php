<?php

namespace Tests\Feature\Admin;

use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/**
 * Clase base para las pruebas del panel de administración.
 *
 * Centraliza la creación del usuario administrador y la siembra de roles
 * que el panel admin exige (middleware 'role:admin|super_admin|Admin').
 */
abstract class BaseAdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Evita que Spatie use el cache de permisos/roles entre pruebas.
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        
        // Sembrar roles y permisos para que las pruebas de middleware no fallen (403).
        $this->seed(\Database\Seeders\RolesSeeder::class);
    }

    /**
     * Crea un administrador de prueba con el rol 'Admin'.
     */
    protected function crearAdmin(array $atributos = []): Usuario
    {
        $admin = Usuario::create(array_merge([
            'nombre' => 'Admin',
            'apellido' => 'Sistema',
            'email' => 'admin.' . uniqid() . '@example.com',
            'password_hash' => Hash::make('secret123'),
            'telefono' => '60000000',
        ], $atributos));

        $admin->assignRole('Admin');

        return $admin;
    }

    /**
     * Crea un super administrador de prueba con el rol 'super_admin' y permisos básicos.
     */
    protected function crearSuperAdmin(array $atributos = []): Usuario
    {
        $superAdmin = Usuario::create(array_merge([
            'nombre' => 'Super',
            'apellido' => 'Admin',
            'email' => 'superadmin.' . uniqid() . '@example.com',
            'password_hash' => Hash::make('secret123'),
            'telefono' => '60000000',
        ], $atributos));

        $superAdmin->assignRole('super_admin');

        return $superAdmin;
    }

    /**
     * Crea un cliente de prueba con el rol 'cliente' (para probar autorización).
     */
    protected function crearCliente(array $atributos = []): Usuario
    {
        $cliente = Usuario::create(array_merge([
            'nombre' => 'Cliente',
            'apellido' => 'Prueba',
            'email' => 'cliente.' . uniqid() . '@example.com',
            'password_hash' => Hash::make('secret123'),
            'telefono' => '60000000',
        ], $atributos));

        $cliente->assignRole('cliente');

        return $cliente;
    }

    /**
     * Crea el rol indicado en la base de pruebas (como se siembra en producción).
     * Nota: Ahora se usa primariamente RolesSeeder en setUp(), pero este método
     * se conserva para tests que necesitan crear roles adicionales.
     */
    protected function crearRol(string $rol): void
    {
        DB::table('roles')->updateOrInsert(
            ['name' => $rol, 'guard_name' => 'web'],
            [
                'nombre' => $rol,
                'activo' => true,
                'creado_en' => now(),
                'actualizado_en' => now(),
            ]
        );
    }

    /**
     * Datos válidos para crear/actualizar un producto (campos del formulario admin).
     */
    protected function datosProductoValidos(array $sobrescribir = []): array
    {
        return array_merge([
            'nombre' => 'Teclado Mecánico RGB',
            'slug' => 'teclado-mecanico-rgb-' . uniqid(),
            'sku' => 'TECLADO-' . strtoupper(uniqid()),
            'categoria_id' => null,
            'precio' => 89.99,
            'descripcion' => 'Teclado mecánico con switches red.',
            'descripcion_corta' => 'Teclado mecánico RGB',
            'stock' => 20,
            'stock_minimo' => 5,
            'activo' => 1,
            'destacado' => 0,
            'aplica_itbms' => 1,
            'oferta_activa' => 0,
        ], $sobrescribir);
    }
}
