<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        // Limpiar caché de permisos de Spatie antes de sembrar
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // ── Permisos del panel admin ──────────────────────────────────────────
        $permisosAdmin = [
            // Dashboard
            'admin.dashboard',

            // Catálogo
            'admin.categorias.ver',   'admin.categorias.crear',
            'admin.categorias.editar','admin.categorias.eliminar',
            'admin.marcas.ver',       'admin.marcas.crear',
            'admin.marcas.editar',    'admin.marcas.eliminar',
            'admin.productos.ver',    'admin.productos.crear',
            'admin.productos.editar', 'admin.productos.eliminar',

            // Pedidos
            'admin.pedidos.ver',      'admin.pedidos.gestionar',

            // Zonas y logística
            'admin.zonas.ver',        'admin.zonas.gestionar',

            // Promociones y cupones
            'admin.cupones.ver',      'admin.cupones.gestionar',
            'admin.promociones.ver',  'admin.promociones.gestionar',
        ];

        // ── Permisos del cliente (storefront) ────────────────────────────────
        $permisosCliente = [
            'cliente.checkout',
            'cliente.perfil.ver',
            'cliente.perfil.editar',
            'cliente.pedidos.ver',
            'cliente.lista-deseos',
        ];

        // Crear todos los permisos
        foreach (array_merge($permisosAdmin, $permisosCliente) as $permiso) {
            Permission::firstOrCreate(
                ['name' => $permiso, 'guard_name' => 'web'],
                ['nombre' => $permiso, 'modulo' => explode('.', $permiso)[0]]
            );
        }

        // ── Rol Admin ────────────────────────────────────────────────────────
        // Nombre 'Admin' coincide con el middleware: role:admin|super_admin|Admin
        $rolAdmin = Role::firstOrCreate(
            ['name' => 'Admin', 'guard_name' => 'web'],
            ['nombre' => 'Administrador', 'descripcion' => 'Acceso total al panel de administración', 'activo' => true]
        );
        $rolAdmin->syncPermissions($permisosAdmin);

        // ── Rol super_admin (acceso total igual que Admin) ───────────────────
        $rolSuperAdmin = Role::firstOrCreate(
            ['name' => 'super_admin', 'guard_name' => 'web'],
            ['nombre' => 'Super Administrador', 'descripcion' => 'Acceso total al sistema', 'activo' => true]
        );
        $rolSuperAdmin->syncPermissions(array_merge($permisosAdmin, $permisosCliente));

        // ── Rol Cliente ──────────────────────────────────────────────────────
        // Asignado automáticamente al registrarse (RegisterController::assignRole('cliente'))
        $rolCliente = Role::firstOrCreate(
            ['name' => 'cliente', 'guard_name' => 'web'],
            ['nombre' => 'Cliente', 'descripcion' => 'Usuario registrado de la tienda', 'activo' => true]
        );
        $rolCliente->syncPermissions($permisosCliente);

        $this->command->info('✅ Roles y permisos creados: Admin, super_admin, cliente');
    }
}
