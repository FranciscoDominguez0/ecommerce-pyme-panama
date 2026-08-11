# AGENTS.md

Guías para agentes de IA y desarrolladores que trabajan en **eCommerce PyME Panamá** (Laravel 12 + Livewire 4 + Vite + Tailwind + PostgreSQL).

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
