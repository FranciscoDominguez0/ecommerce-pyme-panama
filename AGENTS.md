# AGENTS.md

Guías para agentes de IA y desarrolladores que trabajan en **eCommerce PyME Panamá** (Laravel 12 + Livewire 4 + Vite + Tailwind + PostgreSQL).

## Reglas de la base de datos y roles (prevenir pérdida de datos en DEV)

- **NUNCA hardcodees un `rol_id` o `permiso_id` numérico en el código.** Los ids se auto-generan y NO son estables entre entornos, resiembras o resets de BD (ej.: `cliente` fue id 5 en algún momento y es id 3 tras una resiembra). Siempre busca roles/permisos por su **`name`**: `Role::where('name', 'cliente')->where('guard_name', 'web')->first()`, o usa `assignRole('cliente')`/`hasRole('cliente')`. El único lugar que hardcodeaba el id 5 (fallback de `RegisterController`) fue corregido.
- **`RolesSeeder` es la fuente de verdad** de roles (`Admin`, `super_admin`, `cliente`) y permisos. Ejecútalo en toda BD nueva/reseteada (dev o test) antes de depender de funciones basadas en roles: `php artisan db:seed --class=RolesSeeder` (y `RolesPermisosSeeder` para crear/asignar el admin `dominguezf225@gmail.com` como `super_admin`).
- **NUNCA ejecutes `migrate:fresh`, `migrate:refresh` o `db:wipe` sin confirmar antes el `DB_DATABASE` activo.** Los tests SIEMPRE corren contra `ecommerce_test` vía `.env.testing` (`php artisan test --env=testing`); `.env` apunta a `ecommerce_pyme_panama` (dev). Un `migrate:fresh` sin `--env=testing` ejecutado contra dev borró roles/permisos y todo el catálogo (productos, categorías, marcas, variantes, pedidos, etc.) — los 2 usuarios sobrevivientes no tenían seeder que los respaldara. Verifica con `php artisan about` o consultando `config('database.connections.pgsql.database')` antes de cualquier comando destructivo.
- **Los tests están blindados contra la BD dev (doble candado):** `phpunit.xml` fija explícitamente las variables `DB_*` a `ecommerce_test` (no dependen de que `.env.testing` se cargue, ni siquiera al ejecutar `vendor/bin/phpunit` directo), y `tests/TestCase.php` valida en `setUp()` — ANTES de que RefreshDatabase corra `migrate:fresh` — que la BD activa sea exactamente `ecommerce_test`; si no, lanza una excepción y aborta la ejecución sin tocar nada. No cambies esos valores a la BD de desarrollo.
- **Incidente de referencia (2026-08-11):** la BD dev quedó sin roles/permisos y sin catálogo. Se restauraron vía seeders: `RolesSeeder`, `RolesPermisosSeeder`, `BrandSeeder`, `AtributosVarianteSeeder`, `ZonaEnvioSeeder`. **Pendiente de recuperación manual**: `categorias` (no existe `CategoriaSeeder` y `ProductosSeeder` depende de ellas), `productos`/`variantes`/`imagenes` (requieren categorías), y datos transaccionales reales (`pedidos`, `direcciones`, `cupones`, `facturas`, usuarios extra) que no tienen seeder.

## Performance Guidelines

### Resumen de problemas encontrados y corregidos

Una auditoría de rendimiento en el storefront detectó y corrigió lo siguiente:

| Problema | Corrección aplicada |
|---|---|
| CSS compilado en runtime vía **Tailwind Play CDN** (`cdn.tailwindcss.com`) en los layouts — descarga ~330 kB del navegador del usuario en cada carga | Reemplazado por el **pipeline de build de Vite** (`resources/css/app.css`, `resources/js/app.js`, `@vite()` en layouts cliente/admin/guest) |
| Enlaces internos sin `wire:navigate` — cada clic disparaba un full page reload | Se añadió `wire:navigate` a los enlaces internos del storefront (navbar, catálogo, producto, carrito, checkout, perfil, dashboard, paginación) |
| Assets servidos sin cabeceras `Cache-Control` — el navegador los re-descargaba en cada navegación | `public/.htaccess` (Apache/producción) y `server.php` (router de `php artisan serve` en dev) aplican cabeceras por tipo de asset |
| Consultas de carrito duplicadas: `NavbarBadges` y `CarritoDrawer` (y `CarritoWidget`) llamaban cada uno a `obtenerOCrearCarrito()` por separado → 3 `SELECT` de carrito por página | `CarritoService` registrado como **singleton** con **memoización por petición** (`carritosPorPeticion`) e invalidación automática en cada mutación |

### Reglas obligatorias (NO reintroducir estas regresiones)

1. **Nunca** añadas `cdn.tailwindcss.com` ni ningún otro CDN de compilación de CSS/JS en runtime. Todo el CSS/JS debe ir por el **pipeline de build de Vite** (`@vite(['resources/css/app.css', 'resources/js/app.js'])`). Después de modificar `tailwind.config.js` o los `@layer`/`@apply` en CSS, ejecuta `npm run build`.

2. **Todo enlace interno** (`<a href="route(...)">`, `url(...)`, o ruta interna `/...`) dentro del storefront **debe llevar `wire:navigate`** para evitar full page reloads. Excepciones documentadas:
   - Destinos del **panel admin** (admin no carga Livewire: navegación clásica a propósito).
   - Enlaces con `target="_blank"` (abrir en pestaña nueva — `wire:navigate` es irrelevante).
   - Anclas `href="#"` o `#seccion` dentro de la misma página.
   - Páginas guest/auth (login, register, reset) son hojas de entrada: no requieren `wire:navigate`.
   - Si un enlace llega al carrito/checkout desde el **drawer**, recuerda cerrar el drawer antes de navegar (`@click="abierto = false; $wire.cerrar();"`).

3. **Assets estáticos nuevos** (fuentes, imágenes, íconos) deben servirse con cabeceras `Cache-Control` apropiadas. Revisa **ambos** sitios al añadir un tipo de asset nuevo:
   - `public/.htaccess` (Apache — producción): bloques `<IfModule mod_headers.c>` con `<FilesMatch>` / `<If>`.
   - `server.php` (PHP built-in server — dev): `$contentTypes` + bloques `preg_match` de `$cacheControl`.
   - Reglas vigentes: `/build/assets/*.js|css` → `max-age=31536000, immutable` (1 año); fuentes → `604800` (1 semana); `/storage/**` y `/uploads/**` → `86400` (1 día); imágenes/JS/CSS no-fingerprinted → `604800` (1 semana).

4. **Evita fetch duplicado de datos** en múltiples componentes Livewire montados en la misma página (carrito, lista de deseos, notificaciones). En lugar de consultar por componente, **resuelve una vez y comparte** el mismo patrón de `CarritoService` (Fix 4):
   - Registra el servicio como singleton en `AppServiceProvider::register()`.
   - Memoiza dentro de la petición en el método de resolución (`obtenerOCrearCarrito` con `carritosPorPeticion`).
   - **Invalida la caché en toda mutación** (llama al helper de olvido al inicio y fin de `agregarProducto`, `actualizarCantidad`, `eliminarItem`, `aplicarCupon`, `removerCupon`, `fusionarCarritos`, `recalcularDescuentoCupon`), para que los listeners de `carrito-actualizado` en la misma petición lean datos frescos.
   - No añadas relaciones al `->load()` si el servicio ya las eager-loads (provoca una query duplicada — ver open items).

5. **`php artisan config:cache` / `route:cache` son SOLO para producción/despliegue.** Nunca los ejecutes en dev local — cifran `.env` (los cambios de entorno dejan de aplicar hasta `config:clear`) y rompen rutas con closures. `view:cache` es aceptable en dev (se invalida sola por mtime).

### Hallazgos pendientes (open items — no corregidos aún, solo documentados)

- **[wire:navigate × DOMContentLoaded]** Tras ampliar `wire:navigate`, estas páginas del storefront tienen scripts de init con `document.addEventListener('DOMContentLoaded', ...)` que **no se ejecutan** cuando la página se alcanza vía navegación SPA (el evento ya disparó en la carga inicial). Misma clase de bug previamente corregida en `mi-cuenta`:
  - `resources/views/cliente/checkout/pago.blade.php:159` — toggle del formulario de transferencia (crítico: rompe el checkout).
  - `resources/views/cliente/checkout/confirmacion.blade.php:220` — protección de doble submit del botón "Confirmar".
  - `resources/views/components/toast-alert.blade.php:107` — auto-inicio de temporizador de toasts servidos por `->with('toast_*')` (solo afecta toasts renderizados en el primer HTML; los disparados por `Livewire.dispatch` son seguros).
  - **Patrón correcto ya usado en el repo**: `resources/views/cliente/perfil/datos.blade.php` (IIFE + guard `dataset.inited`, sin dependencia de `DOMContentLoaded` para el init crítico). Aplica ese patrón a las páginas listadas.
- **[FIXED] `catalogo/detalle.blade.php` (galería de producto)**: el script tenía `const`/`let` a nivel superior (`galeriaImagenes`, `indiceActual`, `totalImagenes`, `varianteSeleccionadaId`, `touchStartX`) e init con `DOMContentLoaded`. Al re-ejecutarse el script en una navegación SPA (Livewire clona y re-ejecuta los `<script>` del body en scope global), la re-declaración de `const galeriaImagenes` lanzaba `SyntaxError` y abortaba todo el script → imagen anterior/no inicializada. **Corregido** envolviendo todo en una **IIFE** (scope local → sin colisión de `const`/`let`), ejecutando el init de forma **inmediata** (no con `DOMContentLoaded`), exponiendo las funciones usadas por `onclick` inline en `window` (re-enlazadas en cada carga), y registrando el listener `keydown` de `document` **una sola vez** con dispatch a `window.*`. Verificado: navegación A→B→C→A con imagen correcta en cada paso, sin SyntaxError.
- **[items_carrito duplicado]** `CarritoDrawer::cargarCarrito()` llama `->load(['items.producto.imagenes', 'items.producto.brand', 'items.variante.opciones'])` sobre el carrito memoizado. Como `brand` no está en el eager load de `obtenerOCrearCarrito()`, Laravel re-ejecuta `SELECT * FROM items_carrito` → 1 query duplicada por página. Solución sugerida: añadir `items.producto.brand` al eager load del servicio, o comprobar `relationLoaded()` antes de `load()`. Impacto: 1 query extra por página (menor).
- **Servidor dev en el puerto 8000**: el `artisan serve` del usuario se arrancó **antes** de crear `server.php`, por lo que aún usa el router de vendor y **no** aplica las cabeceras de caché. Reiniciar `php artisan serve` (o tocar `.env`) para que use `server.php` y verificar con `curl -I`.
- **[Stock Breeze layout]** `layouts/app.blade.php` + `layouts/navigation.blade.php` (componentes `AppLayout`, `GuestLayout`) son restos de Laravel Breeze usados solo por `profile/edit.blade.php` (sin ruta activa). Usan `@vite` (sin CDN) pero **no** tienen `wire:navigate`. No afecta al rendimiento actual (código muerto), pero conviene eliminarlos o migrarlos.
- **[CANCELAR NO restaura stock — pendiente de confirmación de negocio]** Al cancelar un pedido (`cambiarEstado('cancelado')`) NO se repone el stock descontado al crearlo. Si la decisión de negocio es que sí debe reponerse (y también en `devolucion_aprobada`), implementar: incrementar stock por item + registrar `movimientos_inventario` tipo `entrada` con motivo `"Cancelación - Pedido #PM-XXXXXX"`. Hasta que se confirme, el comportamiento actual (no restaurar) queda documentado por el test `test_cancelar_un_pedido_no_restaura_el_stock`.

### Comportamientos confirmados como intencionales (no corregir)

- **contra_entrega** se confirma de inmediato como pedido en estado `pendiente` (el pago se cobra al entregar) — intencional.
- **transferencia (ACH)** exige subir el comprobante antes de crear el pedido; sin él `PagoService::procesarTransferencia(null)` devuelve `false` y NO se crea el pedido (queda a confirmación manual del admin) — intencional.
- **Stripe / Yappy** son **simulaciones puras** (`PagoService` siempre devuelve `true`; nunca se llama a APIs reales). **TODO**: integrar SDKs reales de pasarela de pago antes de producción. Los tests fuerzan el fallo con mock de `PagoService`.
- **Aislamiento por usuario**: `Cliente\PedidoController::detalle` filtra por `usuario_id` (404 cross-user) — correcto.
- **Cupón en carrito**: la integración real está en `CarritoWidget::aplicarCupon` y `CarritoController::aplicarCupon` (cubiertas por tests). Los métodos muertos `verCarrito`/`aplicarCuponCarrito`/`removerCuponCarrito` de `PromocionController` (sin rutas registradas) fueron **ELIMINADOS**.

### Notas de arquitectura resueltas

- **[FIXED — numero_pedido] Fuente única de verdad en PHP.** Existían DOS mecanismos en conflicto: el trigger DB `trg_numero_pedido`/`generar_numero_pedido()` (formato `P-YYYY-000001`) y el código PHP de `PedidoService` que lo sobrescribía con `#PM-XXXXXX`. Se eliminó el trigger (migración `2026_08_11_000000_drop_trigger_numero_pedido.php`) y `numero_pedido` lo genera **únicamente** `PedidoService::generarNumeroPedido()`: correlativo atómico en `configuracion` (`clave = 'pedido_correlativo'`) con `lockForUpdate()` dentro de la transacción del pedido (mismo patrón seguro que `facturas.numero` → `generar_numero_factura`). Esto evita la colisión que ocurriría si dos pedidos insertaran `numero_pedido = ''` de forma concurrente (constraint `pedidos_numero_pedido_key`). Si un día se regenera la BD desde cero, los números siguen saliendo `#PM-260001`, `#PM-260002`, … porque el correlativo se siembra con `MAX(pedidos.id)`.
- **[FIXED — IDOR en items del carrito (seguridad)]** `CarritoService::actualizarCantidad()` y `eliminarItem()` ahora validan la propiedad del item antes de actuar (`validarPropietarioDelItem`): el carrito del item debe pertenecer al `usuario_id` autenticado o al `sesion_id` de la sesión actual. Se añadió el parámetro `?string $sesionId` y se actualizaron TODOS los llamadores (rutas HTTP, `CarritoWidget`, `CarritoDrawer`). Antes, cualquier usuario podía modificar/eliminar items ajenos adivinando el id.
- **[FIXED — movimientos_inventario (auditoría de stock)]** `PedidoService::crearDesdeCarrito` ahora registra un `movimientos_inventario` (tipo `salida`, `stock_antes`/`stock_despues`, `pedido_id`, motivo `"Venta - Pedido #PM-XXXXXX"`) por cada item, en la MISMA transacción que descuenta stock. Antes la tabla existía pero ningún código la escribía.
- **[FIXED — ITBMS respeta aplica_itbms]** Se extrajo el cálculo compartido `CarritoService::calcularSubtotalEItbms()` (subtotal + base imponible ITBMS por producto con `aplica_itbms`). `PedidoService::calcularTotales` y `CarritoService::calcularTotal` lo usan → carrito y pedido siempre consistentes (antes PedidoService aplicaba 7% plano sobre todo el subtotal).
- **[FIXED — estado inicial 'pendiente' duplicado]** El trigger DB `trg_estado_inicial_pedido`/`registrar_estado_inicial_pedido()` fue eliminado (migración `2026_08_11_000001_drop_trigger_estado_inicial_pedido.php`). El estado inicial y todas las transiciones las gestiona **únicamente** `PedidoService::cambiarEstado` → exactamente 1 fila `pendiente` al crear el pedido.
