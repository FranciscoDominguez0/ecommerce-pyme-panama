<x-mail::message>
# ¡Hola, {{ $pedido->usuario->nombre }}!

Han pasado algunos días desde que enviamos tu pedido **{{ $pedido->numero_pedido }}** y queríamos asegurarnos de que ya esté en tus manos.

Si ya recibiste tu paquete correctamente, por favor ayúdanos a confirmar la entrega haciendo clic en el siguiente botón:

<x-mail::button :url="route('cliente.perfil.pedidos.detalle', $pedido->id)">
Confirmar Recepción
</x-mail::button>

Si aún no lo has recibido, no te preocupes, el paquete sigue en ruta. Puedes rastrearlo o ver más detalles en tu perfil.

Gracias por comprar con nosotros,<br>
El equipo de {{ config('app.name') }}
</x-mail::message>
