<?php

namespace Tests\Feature\Auth;

use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Pruebas del módulo de CAMBIO DE CONTRASEÑA desde el perfil (Mi Cuenta).
 *
 * Cubre la ruta real de la aplicación: PUT /mi-cuenta/password
 * (PerfilController@updatePassword → route 'cliente.perfil.password.update'),
 * que se muestra en /mi-cuenta/password (vista cliente/perfil/password.blade.php).
 *
 * Configuración de pruebas: Laravel carga `.env.testing` (APP_ENV=testing),
 * que apunta a la base de pruebas dedicada `ecommerce_test`. `RefreshDatabase`
 * ejecuta `migrate:fresh` contra esa base y envuelve cada prueba en una
 * transacción que se revierte — nunca toca los datos de desarrollo/producción.
 */
class ActualizarContrasenaTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Crea un usuario de prueba con una contraseña conocida.
     */
    protected function crearUsuario(array $atributos = []): Usuario
    {
        return Usuario::create(array_merge([
            'nombre' => 'Juan',
            'apellido' => 'Pérez',
            'email' => 'juan.' . uniqid() . '@example.com',
            'password_hash' => Hash::make('secret123'),
            'telefono' => '60000000',
        ], $atributos));
    }

    // =====================================================================
    //  DISEÑO — La vista GET /mi-cuenta/password
    // =====================================================================

    public function test_la_vista_de_cambio_de_contrasena_se_renderiza_para_autenticados(): void
    {
        $usuario = $this->crearUsuario();

        $this->actingAs($usuario)
            ->get('/mi-cuenta/password')
            ->assertOk()
            ->assertSee('Cambiar Contraseña')
            ->assertSee('Actualiza la contraseña de tu cuenta.');
    }

    public function test_la_vista_de_cambio_de_contrasena_tiene_los_tres_campos(): void
    {
        $usuario = $this->crearUsuario();

        $this->actingAs($usuario)
            ->get('/mi-cuenta/password')
            ->assertOk()
            ->assertSee('id="current_password"', false)
            ->assertSee('name="current_password"', false)
            ->assertSee('id="password"', false)
            ->assertSee('name="password"', false)
            ->assertSee('id="password_confirmation"', false)
            ->assertSee('name="password_confirmation"', false);
    }

    public function test_la_vista_de_cambio_de_contrasena_envia_el_formulario_a_la_ruta_correcta(): void
    {
        $usuario = $this->crearUsuario();

        $this->actingAs($usuario)
            ->get('/mi-cuenta/password')
            ->assertOk()
            ->assertSee('action="' . route('cliente.perfil.password.update') . '"', false)
            ->assertSee('method="POST"', false)
            ->assertSee('name="_method" value="PUT"', false)
            ->assertSee('name="_token"', false);
    }

    public function test_la_vista_de_cambio_de_contrasena_requiere_estar_autenticado(): void
    {
        $this->get('/mi-cuenta/password')
            ->assertRedirect('/login');
    }

    // =====================================================================
    //  LÓGICA — PUT /mi-cuenta/password
    // =====================================================================

    public function test_una_contrasena_se_puede_actualizar_con_la_contrasena_actual_correcta(): void
    {
        $usuario = $this->crearUsuario();

        $respuesta = $this
            ->actingAs($usuario)
            ->from('/mi-cuenta/password')
            ->put('/mi-cuenta/password', [
                'current_password' => 'secret123',
                'password' => 'NuevaContraseña123',
                'password_confirmation' => 'NuevaContraseña123',
            ]);

        $respuesta
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('cliente.perfil.datos'));

        // El hash de la contraseña se actualizó en la tabla usuarios.
        $usuario->refresh();
        $this->assertTrue(Hash::check('NuevaContraseña123', $usuario->password_hash));
        $this->assertFalse(Hash::check('secret123', $usuario->password_hash));
    }

    public function test_se_requiere_la_contrasena_actual_correcta_para_actualizar(): void
    {
        $usuario = $this->crearUsuario();

        $respuesta = $this
            ->actingAs($usuario)
            ->from('/mi-cuenta/password')
            ->put('/mi-cuenta/password', [
                'current_password' => 'contrasena-incorrecta',
                'password' => 'NuevaContraseña123',
                'password_confirmation' => 'NuevaContraseña123',
            ]);

        $respuesta
            ->assertSessionHasErrors([
                'current_password' => 'La contraseña actual no es correcta.',
            ])
            ->assertRedirect('/mi-cuenta/password');

        // La contraseña no cambió.
        $usuario->refresh();
        $this->assertTrue(Hash::check('secret123', $usuario->password_hash));
    }

    public function test_la_nueva_contrasena_debe_tener_al_menos_8_caracteres(): void
    {
        $usuario = $this->crearUsuario();

        $respuesta = $this
            ->actingAs($usuario)
            ->from('/mi-cuenta/password')
            ->put('/mi-cuenta/password', [
                'current_password' => 'secret123',
                'password' => 'corto',
                'password_confirmation' => 'corto',
            ]);

        $respuesta
            ->assertSessionHasErrors('password')
            ->assertRedirect('/mi-cuenta/password');

        $usuario->refresh();
        $this->assertTrue(Hash::check('secret123', $usuario->password_hash));
    }

    public function test_la_nueva_contrasena_debe_coincidir_con_su_confirmacion(): void
    {
        $usuario = $this->crearUsuario();

        $respuesta = $this
            ->actingAs($usuario)
            ->from('/mi-cuenta/password')
            ->put('/mi-cuenta/password', [
                'current_password' => 'secret123',
                'password' => 'NuevaContraseña123',
                'password_confirmation' => 'OtraContraseña456',
            ]);

        $respuesta
            ->assertSessionHasErrors('password')
            ->assertRedirect('/mi-cuenta/password');

        $usuario->refresh();
        $this->assertTrue(Hash::check('secret123', $usuario->password_hash));
    }
}
