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
            'admin.pedidos.ver',      'admin.pedidos.crear',
            'admin.pedidos.editar',   'admin.pedidos.eliminar', 'admin.pedidos.reembolsar',

            // Zonas y logística
            'admin.zonas.ver',        'admin.zonas.crear',
            'admin.zonas.editar',     'admin.zonas.eliminar',

            // Promociones y cupones
            'admin.cupones.ver',      'admin.cupones.crear',
            'admin.cupones.editar',   'admin.cupones.eliminar',
            'admin.promociones.ver',  'admin.promociones.crear',
            'admin.promociones.editar', 'admin.promociones.eliminar',

            // Usuarios y Roles
            'admin.usuarios.ver',     'admin.usuarios.crear',
            'admin.usuarios.editar',  'admin.usuarios.eliminar',

            // Módulos Adicionales (Devoluciones, Facturas, Auditoria, Config, Inventario, Reportes)
            'admin.devoluciones.ver', 'admin.devoluciones.crear', 'admin.devoluciones.editar', 'admin.devoluciones.eliminar',
            'admin.facturas.ver',     'admin.facturas.crear',     'admin.facturas.editar',     'admin.facturas.eliminar',
            'admin.auditoria.ver',    'admin.auditoria.crear',    'admin.auditoria.editar',    'admin.auditoria.eliminar',
            'admin.configuracion.ver','admin.configuracion.crear','admin.configuracion.editar','admin.configuracion.eliminar',
            'admin.inventario.ver',   'admin.inventario.crear',   'admin.inventario.editar',   'admin.inventario.eliminar',
            'admin.reportes.ver',     'admin.reportes.crear',     'admin.reportes.editar',     'admin.reportes.eliminar',
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
            $parts = explode('.', $permiso);
            $modulo = $parts[0];
            
            if ($parts[0] === 'admin') {
                $modulo = count($parts) >= 3 ? $parts[1] : $parts[1];
            } else if ($parts[0] === 'cliente') {
                $modulo = 'cliente';
            }

            Permission::firstOrCreate(
                ['name' => $permiso, 'guard_name' => 'web'],
                ['nombre' => $permiso, 'modulo' => $modulo]
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
