<?php

namespace Tests\Feature\Livewire;

use App\Livewire\CheckoutEnvio;
use App\Models\Carrito;
use App\Models\Direccion;
use App\Models\ItemCarrito;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Usuario;
use Livewire\Livewire;
use Tests\Feature\Admin\BaseAdminTest;

class CheckoutEnvioTest extends BaseAdminTest
{
    protected function crearCarritoConItem(Usuario $usuario, Producto $producto, int $cantidad, float $precio): ItemCarrito
    {
        $carrito = Carrito::factory()->create(['usuario_id' => $usuario->id]);

        return ItemCarrito::factory()->create([
            'carrito_id' => $carrito->id,
            'producto_id' => $producto->id,
            'cantidad' => $cantidad,
            'precio_unitario' => $precio,
        ]);
    }
    public function test_dispara_toast_cuando_falta_telefono(): void
    {
        $usuario = $this->crearCliente();
        $producto = Producto::factory()->create(['stock' => 10]);
        $this->crearCarritoConItem($usuario, $producto, 1, 50.00);

        Livewire::actingAs($usuario)
            ->test(CheckoutEnvio::class)
            ->set('contacto_nombre', 'Carlos')
            ->set('contacto_apellido', 'Perez')
            ->set('contacto_email', 'carlos@test.com')
            ->set('contacto_telefono1', '')
            ->call('continuarCheckout')
            ->assertHasErrors(['contacto_telefono1'])
            ->assertDispatched('mostrar-toast', function ($event, $params) {
                $mensaje = $params['mensaje'] ?? ($params[0]['mensaje'] ?? '');
                return str_contains($mensaje, 'Falta ingresar el número de teléfono');
            });
    }

    public function test_dispara_toast_cuando_falta_direccion_en_delivery(): void
    {
        $usuario = $this->crearCliente();
        $producto = Producto::factory()->create(['stock' => 10]);
        $this->crearCarritoConItem($usuario, $producto, 1, 50.00);

        session()->forget('checkout_direccion_id');

        Livewire::actingAs($usuario)
            ->test(CheckoutEnvio::class)
            ->set('contacto_nombre', 'Carlos')
            ->set('contacto_apellido', 'Perez')
            ->set('contacto_email', 'carlos@test.com')
            ->set('contacto_telefono1', '6789-0000')
            ->set('metodo_entrega', 'delivery')
            ->call('continuarCheckout')
            ->assertHasErrors(['metodo_entrega'])
            ->assertDispatched('open-modal', 'modal-calculadora-envio')
            ->assertDispatched('mostrar-toast', function ($event, $params) {
                $mensaje = $params['mensaje'] ?? ($params[0]['mensaje'] ?? '');
                return str_contains($mensaje, 'Falta seleccionar la dirección');
            });
    }

    public function test_continua_exitosamente_cuando_datos_y_direccion_estan_completos(): void
    {
        $usuario = $this->crearCliente();
        $producto = Producto::factory()->create(['stock' => 10]);
        $this->crearCarritoConItem($usuario, $producto, 1, 50.00);
        $direccion = Direccion::factory()->create(['usuario_id' => $usuario->id]);

        session(['checkout_direccion_id' => $direccion->id]);

        Livewire::actingAs($usuario)
            ->test(CheckoutEnvio::class)
            ->set('contacto_nombre', 'Carlos')
            ->set('contacto_apellido', 'Perez')
            ->set('contacto_email', 'carlos@test.com')
            ->set('contacto_telefono1', '6789-0000')
            ->set('metodo_entrega', 'delivery')
            ->call('continuarCheckout')
            ->assertHasNoErrors()
            ->assertRedirect(route('cliente.checkout.pago'));

        $this->assertSame('Carlos', session('checkout_contacto_nombre'));
        $this->assertSame('6789-0000', session('checkout_contacto_telefono1'));
    }

    public function test_autoselecciona_direccion_guardada_de_la_bd_cuando_sesion_esta_vacia(): void
    {
        $usuario = $this->crearCliente();
        $producto = Producto::factory()->create(['stock' => 10]);
        $this->crearCarritoConItem($usuario, $producto, 1, 50.00);

        $direccion = Direccion::factory()->create([
            'usuario_id' => $usuario->id,
            'es_predeterminada' => true,
        ]);

        session()->forget('checkout_direccion_id');

        Livewire::actingAs($usuario)
            ->test(CheckoutEnvio::class)
            ->call('continuarCheckout')
            ->assertHasNoErrors()
            ->assertRedirect(route('cliente.checkout.pago'));

        $this->assertSame($direccion->id, session('checkout_direccion_id'));
    }

    public function test_autoselecciona_direccion_del_ultimo_pedido_cuando_hace_otra_compra(): void
    {
        $usuario = $this->crearCliente();
        $producto = Producto::factory()->create(['stock' => 10]);
        $this->crearCarritoConItem($usuario, $producto, 1, 50.00);

        $direccionVieja = Direccion::factory()->create([
            'usuario_id' => $usuario->id,
            'alias' => 'Dirección Antigua',
            'es_predeterminada' => true,
        ]);

        $direccionUltimoPedido = Direccion::factory()->create([
            'usuario_id' => $usuario->id,
            'alias' => 'Dirección Usada en Compra',
            'es_predeterminada' => false,
        ]);

        // Simulamos compra anterior del usuario con $direccionUltimoPedido
        Pedido::factory()->create([
            'usuario_id' => $usuario->id,
            'direccion_id' => $direccionUltimoPedido->id,
            'creado_en' => now()->subDay(),
        ]);

        // Nueva sesión de compra sin nada en checkout_direccion_id
        session()->forget('checkout_direccion_id');

        Livewire::actingAs($usuario)
            ->test(CheckoutEnvio::class)
            ->call('continuarCheckout')
            ->assertHasNoErrors()
            ->assertRedirect(route('cliente.checkout.pago'));

        $this->assertSame($direccionUltimoPedido->id, session('checkout_direccion_id'));
    }
}
