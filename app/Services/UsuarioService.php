<?php

namespace App\Services;

use App\Models\Role;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class UsuarioService
{
    // Calcula métricas de compras y pedidos para la vista de detalle
    public function calcularMetricasUsuario(Usuario $usuario): array
    {
        $pedidos = $usuario->pedidos()->with('ultimoEstado')->get();
        $totalPedidos = $pedidos->count();
        $totalGastado = (float) $pedidos->reject(function ($pedido) {
            $estado = strtolower($pedido->ultimoEstado?->estado ?? '');
            return in_array($estado, ['cancelado', 'rechazado', 'devuelto'], true);
        })->sum('total');

        return [
            'totalPedidos' => $totalPedidos,
            'totalGastado' => $totalGastado,
        ];
    }

    // Registra un nuevo usuario con contraseña aleatoria y envía correo de activación/restablecimiento
    public function crear(array $datos, bool $activo): Usuario
    {
        $usuario = new Usuario();
        $usuario->nombre = $datos['nombre'];
        $usuario->apellido = $datos['apellido'] ?? null;
        $usuario->email = $datos['email'];
        $usuario->password_hash = Hash::make(Str::random(32));
        $usuario->telefono = $datos['telefono'] ?? null;
        $usuario->activo = $activo;
        $usuario->save();

        $rolAsignar = Role::findOrFail($datos['rol_id']);
        $usuario->assignRole($rolAsignar->name);

        Password::sendResetLink(['email' => $usuario->email]);

        AuditoriaService::registrar(
            'Usuarios',
            'crear',
            "Usuario '{$usuario->nombre_completo}' ({$usuario->email}) creado con rol '{$rolAsignar->name}'.",
            null,
            $usuario->only(['id', 'nombre', 'apellido', 'email', 'activo'])
        );

        return $usuario;
    }

    // Actualiza los datos de un usuario y sincroniza su rol
    public function actualizar(Usuario $usuario, array $datos, bool $activo): Usuario
    {
        $valorAnterior = $usuario->only(['nombre', 'apellido', 'email', 'telefono', 'activo']);

        $usuario->nombre = $datos['nombre'];
        $usuario->apellido = $datos['apellido'] ?? null;
        $usuario->email = $datos['email'];
        if (!empty($datos['password'])) {
            $usuario->password_hash = Hash::make($datos['password']);
        }
        $usuario->telefono = $datos['telefono'] ?? null;
        $usuario->activo = $activo;
        $usuario->save();

        $rolAsignar = Role::findOrFail($datos['rol_id']);
        $usuario->syncRoles([$rolAsignar->name]);

        AuditoriaService::registrar(
            'Usuarios',
            'actualizar',
            "Usuario '{$usuario->nombre_completo}' actualizado.",
            $valorAnterior,
            $usuario->only(['nombre', 'apellido', 'email', 'telefono', 'activo'])
        );

        return $usuario;
    }

    // Envía correo de restablecimiento de contraseña para que el usuario defina su propia clave
    public function enviarEnlaceRestablecimiento(Usuario $usuario): bool
    {
        $token = Str::random(64);

        // Guardar o actualizar en la tabla password_reset_tokens
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $usuario->email],
            [
                'token' => Hash::make($token),
                'created_at' => now(),
            ]
        );

        $resetUrl = route('password.reset', [
            'token' => $token,
            'email' => $usuario->email,
        ]);

        $emailDestino = $usuario->email;
        CorreoService::despacharEnSegundoPlano(function () use ($usuario, $token, $emailDestino, $resetUrl) {
            Mail::send('emails.reset-password', [
                'usuario' => $usuario,
                'token' => $token,
                'email' => $emailDestino,
                'resetUrl' => $resetUrl,
            ], function ($message) use ($emailDestino) {
                $message->to($emailDestino)
                        ->subject('Restablece tu contraseña - PayMe Panamá');
            });
        }, 'Restablecimiento de contraseña desde administración');

        AuditoriaService::registrar(
            'Usuarios',
            'restablecer_clave',
            "Se envió enlace de restablecimiento de contraseña a '{$usuario->nombre_completo}' ({$usuario->email}).",
            null,
            ['id' => $usuario->id, 'email' => $usuario->email]
        );

        return true;
    }

    // Elimina en cascada segura todas las relaciones dependientes y luego al usuario
    public function eliminarEnCascada(Usuario $usuario): void
    {
        $datosAnteriores = $usuario->only(['id', 'nombre', 'apellido', 'email']);

        DB::transaction(function () use ($usuario) {
            $userId = $usuario->id;

            // Eliminar facturas y sus reenvíos para evitar restricciones de clave foránea
            $facturasIds = DB::table('facturas')->where('usuario_id', $userId)->pluck('id');
            if ($facturasIds->isNotEmpty()) {
                DB::table('reenvios_factura')->whereIn('factura_id', $facturasIds)->delete();
                DB::table('facturas')->where('usuario_id', $userId)->delete();
            }

            // Eliminar devoluciones
            DB::table('devoluciones')->where('usuario_id', $userId)->delete();

            // Eliminar historial de uso de cupones
            if (Schema::hasTable('usos_cupon')) {
                DB::table('usos_cupon')->where('usuario_id', $userId)->delete();
            }

            // Eliminar pedidos asociados
            DB::table('pedidos')->where('usuario_id', $userId)->delete();

            // Eliminar usuario final
            $usuario->delete();
        });

        AuditoriaService::registrar(
            'Usuarios',
            'eliminar',
            "Usuario '{$usuario->nombre_completo}' ({$usuario->email}) eliminado permanentemente.",
            $datosAnteriores,
            null
        );
    }
}
