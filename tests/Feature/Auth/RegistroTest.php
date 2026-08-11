<?php

namespace Tests\Feature\Auth;

use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/**
 * Pruebas del módulo de REGISTRO (FASE 3).
 *
 * Configuración de pruebas: Laravel carga `.env.testing` (APP_ENV=testing),
 * que apunta a la base de pruebas dedicada `ecommerce_test`. `RefreshDatabase`
 * ejecuta `migrate:fresh` contra esa base y envuelve cada prueba en una
 * transacción que se revierte — nunca toca los datos de desarrollo/producción.
 */
class RegistroTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Evita que Spatie use el cache de permisos/roles entre pruebas.
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * Crea el rol indicado en la base de pruebas (como se siembra en producción).
     */
    protected function crearRol(string $rol = 'cliente'): void
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
     * Datos válidos de registro para un cliente.
     */
    protected function datosRegistroValidos(array $sobrescribir = []): array
    {
        return array_merge([
            'nombre' => 'María',
            'apellido' => 'González',
            'telefono' => '60001234',
            'email' => 'maria.' . uniqid() . '@example.com',
            'password' => 'ContraseñaSegura123',
            'password_confirmation' => 'ContraseñaSegura123',
            'terms' => '1',
        ], $sobrescribir);
    }

    // =====================================================================
    //  DISEÑO — La vista GET /register debe renderizarse con todos los campos
    // =====================================================================

    public function test_la_vista_de_registro_se_renderiza_correctamente(): void
    {
        $this->get('/register')
            ->assertOk()
            ->assertSee('Crear Cuenta Nueva')
            ->assertSee('PayMe Panamá');
    }

    public function test_la_vista_de_registro_tiene_campo_de_nombre(): void
    {
        $this->get('/register')
            ->assertOk()
            ->assertSee('id="nombre"', false)
            ->assertSee('name="nombre"', false)
            ->assertSee('>person</span>', false);
    }

    public function test_la_vista_de_registro_tiene_campo_de_apellido(): void
    {
        $this->get('/register')
            ->assertOk()
            ->assertSee('id="apellido"', false)
            ->assertSee('name="apellido"', false);
    }

    public function test_la_vista_de_registro_tiene_campo_de_telefono(): void
    {
        $this->get('/register')
            ->assertOk()
            ->assertSee('id="telefono"', false)
            ->assertSee('name="telefono"', false);
    }

    public function test_la_vista_de_registro_tiene_campo_de_correo(): void
    {
        $this->get('/register')
            ->assertOk()
            ->assertSee('id="email"', false)
            ->assertSee('type="email"', false)
            ->assertSee('name="email"', false);
    }

    public function test_la_vista_de_registro_tiene_campo_de_contrasena(): void
    {
        $this->get('/register')
            ->assertOk()
            ->assertSee('id="password"', false)
            ->assertSee('type="password"', false)
            ->assertSee('name="password"', false)
            ->assertSee('Mínimo 8 caracteres');
    }

    public function test_la_vista_de_registro_tiene_campo_de_confirmar_contrasena(): void
    {
        $this->get('/register')
            ->assertOk()
            ->assertSee('id="password_confirmation"', false)
            ->assertSee('name="password_confirmation"', false);
    }

    public function test_la_vista_de_registro_tiene_checkbox_de_terminos(): void
    {
        $this->get('/register')
            ->assertOk()
            ->assertSee('id="terms"', false)
            ->assertSee('name="terms"', false)
            ->assertSee('He leído y acepto los');
    }

    public function test_la_vista_de_registro_tiene_boton_de_envio(): void
    {
        $this->get('/register')
            ->assertOk()
            ->assertSee('Crear mi cuenta');
    }

    public function test_la_vista_de_registro_tiene_enlace_a_inicio_de_sesion(): void
    {
        $this->get('/register')
            ->assertOk()
            ->assertSee('¿Ya tienes una cuenta?')
            ->assertSee('Iniciar Sesión')
            ->assertSee('href="' . route('login') . '"', false);
    }

    public function test_el_formulario_de_registro_incluye_token_csrf(): void
    {
        $this->get('/register')
            ->assertOk()
            ->assertSee('name="_token"', false);
    }

    // =====================================================================
    //  VALIDACIÓN — errores esperados al enviar datos incompletos o inválidos
    // =====================================================================

    public function test_el_registro_requiere_nombre(): void
    {
        $respuesta = $this->from('/register')->post('/register', $this->datosRegistroValidos([
            'nombre' => '',
        ]));

        $respuesta->assertSessionHasErrors([
            'nombre' => 'El nombre es obligatorio.',
        ]);
        $this->assertGuest();
    }

    public function test_el_registro_requiere_apellido(): void
    {
        $respuesta = $this->from('/register')->post('/register', $this->datosRegistroValidos([
            'apellido' => '',
        ]));

        $respuesta->assertSessionHasErrors([
            'apellido' => 'El apellido es obligatorio.',
        ]);
        $this->assertGuest();
    }

    public function test_el_registro_requiere_correo(): void
    {
        $respuesta = $this->from('/register')->post('/register', $this->datosRegistroValidos([
            'email' => '',
        ]));

        $respuesta->assertSessionHasErrors([
            'email' => 'El correo electrónico es obligatorio.',
        ]);
        $this->assertGuest();
    }

    public function test_el_registro_rechaza_un_correo_mal_formado(): void
    {
        $respuesta = $this->from('/register')->post('/register', $this->datosRegistroValidos([
            'email' => 'no-es-un-correo',
        ]));

        $respuesta->assertSessionHasErrors([
            'email' => 'Ingrese un correo electrónico válido.',
        ]);
        $this->assertGuest();
    }

    public function test_el_registro_rechaza_un_correo_duplicado_en_usuarios(): void
    {
        $emailExistente = 'ya-registrado' . uniqid() . '@example.com';
        Usuario::create([
            'nombre' => 'Existente',
            'apellido' => 'Usuario',
            'email' => $emailExistente,
            'password_hash' => Hash::make('password'),
            'telefono' => '60000000',
        ]);

        $respuesta = $this->from('/register')->post('/register', $this->datosRegistroValidos([
            'email' => $emailExistente,
        ]));

        $respuesta->assertSessionHasErrors([
            'email' => 'Este correo electrónico ya se encuentra registrado.',
        ]);
        $this->assertGuest();
    }

    public function test_el_registro_requiere_contrasena(): void
    {
        $respuesta = $this->from('/register')->post('/register', $this->datosRegistroValidos([
            'password' => '',
            'password_confirmation' => '',
        ]));

        $respuesta->assertSessionHasErrors([
            'password' => 'La contraseña es obligatoria.',
        ]);
        $this->assertGuest();
    }

    public function test_el_registro_requiere_contrasena_minima_de_8_caracteres(): void
    {
        $respuesta = $this->from('/register')->post('/register', $this->datosRegistroValidos([
            'password' => 'corto',
            'password_confirmation' => 'corto',
        ]));

        $respuesta->assertSessionHasErrors([
            'password' => 'La contraseña debe contener al menos 8 caracteres.',
        ]);
        $this->assertGuest();
    }

    public function test_el_registro_requiere_que_la_contrasena_coincida_con_su_confirmacion(): void
    {
        $respuesta = $this->from('/register')->post('/register', $this->datosRegistroValidos([
            'password' => 'ContraseñaSegura123',
            'password_confirmation' => 'OtraContraseña456',
        ]));

        $respuesta->assertSessionHasErrors([
            'password' => 'La confirmación de la contraseña no coincide.',
        ]);
        $this->assertGuest();
    }

    public function test_el_registro_requiere_aceptar_los_terminos_y_condiciones(): void
    {
        $respuesta = $this->from('/register')->post('/register', $this->datosRegistroValidos([
            'terms' => null,
        ]));

        $respuesta->assertSessionHasErrors([
            'terms' => 'Debe aceptar los términos y condiciones para registrarse.',
        ]);
        $this->assertGuest();
    }

    // =====================================================================
    //  LÓGICA — registro exitoso: creación, rol, autenticación y redirección
    // =====================================================================

    public function test_un_cliente_se_puede_registrar_exitosamente(): void
    {
        $this->crearRol('cliente');
        $datos = $this->datosRegistroValidos();

        $respuesta = $this->post('/register', $datos);

        // Se redirige al dashboard (la ruta /home es un alias que desemboca ahí).
        $respuesta->assertRedirect(route('dashboard'));

        // El usuario quedó autenticado.
        $this->assertAuthenticated();

        // Se creó un registro en la tabla usuarios.
        $usuario = Usuario::where('email', $datos['email'])->first();
        $this->assertNotNull($usuario, 'No se creó el registro en la tabla usuarios.');
        $this->assertSame('María', $usuario->nombre);
        $this->assertSame('González', $usuario->apellido);
        $this->assertSame($datos['telefono'], $usuario->telefono);

        // La contraseña se almacenó con hash bcrypt (no en texto plano).
        $this->assertNotSame($datos['password'], $usuario->password_hash);
        $this->assertTrue(Hash::check($datos['password'], $usuario->password_hash));
    }

    public function test_el_registro_asigna_automaticamente_el_rol_cliente(): void
    {
        $this->crearRol('cliente');
        $datos = $this->datosRegistroValidos();

        $this->post('/register', $datos);

        $usuario = Usuario::where('email', $datos['email'])->first();
        $this->assertNotNull($usuario);
        $this->assertTrue($usuario->hasRole('cliente'), 'Se esperaba que el usuario tuviera el rol cliente.');
    }

    public function test_el_usuario_queda_autenticado_tras_registrarse(): void
    {
        $this->crearRol('cliente');
        $datos = $this->datosRegistroValidos();

        $this->post('/register', $datos);

        $usuario = Usuario::where('email', $datos['email'])->first();
        $this->assertAuthenticatedAs($usuario);
    }

    public function test_el_registro_redirige_a_la_ruta_dashboard(): void
    {
        $this->crearRol('cliente');
        $datos = $this->datosRegistroValidos();

        $respuesta = $this->post('/register', $datos);

        // La especificación indica "/home"; funcionalmente /home redirige a
        // /dashboard, que es la ruta a la que apunta el controlador.
        $respuesta->assertRedirect('/dashboard');
    }
}
