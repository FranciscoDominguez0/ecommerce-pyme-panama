# AGENTS.md

## Proyecto

**PayMe Panamá — eCommerce PyME**
Stack: **Laravel 12 + Livewire 4 + Vite + Tailwind + PostgreSQL**.

Este archivo define las convenciones obligatorias para agentes de IA y desarrolladores. Antes de modificar código, respetar estas reglas y reutilizar los componentes, servicios y patrones existentes.

---

# 1. UI, diseño e identidad visual

### Tipografía

* Fuente oficial: **Plus Jakarta Sans** (`font-sans`).
* Está configurada globalmente en `resources/views/layouts/admin.blade.php`.
* No importar fuentes adicionales desde vistas individuales.
* Mantener `font-sans` y la jerarquía tipográfica definida por el layout.

### Colores

* Accent principal: `emerald-600` / `#059669`.
* Fondo/superficie: `slate-50` / `#F8FAFC`.
* Bordes: `slate-200` / `#E5E7EB`.
* Sidebar: `#1F2937`.
* Texto del sidebar: `slate-300`.
* Indicadores activos: `emerald-400`.

### Navegación administrativa

El breadcrumb del TopBar debe seguir:

`Panel > Módulo > Acción`

Ejemplos de acción: `Index`, `Nuevo`, `Editar`.

El TopNavBar permanece fijo:

```html
sticky top-0 z-40 backdrop-blur-md
```

---

# 2. Componentes globales obligatorios

### Toast / notificaciones

Utilizar:

```blade
<x-toast-alert />
```

Está incluido en `layouts/admin.blade.php`.

**No crear alertas inline** como:

```blade
@if(session('success'))
@if(session('error'))
```

El sistema global ya procesa:

* `success`
* `toast_success`
* `error`
* `warning`
* `info`

Crear alertas adicionales puede provocar mensajes duplicados.

### Confirmación de eliminación

Utilizar:

```blade
<x-modal-eliminar />
```

y:

```js
window.ModalEliminar.abrir(url, nombre, extra)
```

o:

```js
window.ModalEliminar.abrir({
    url,
    nombre,
    extra,
    titulo,
    mensaje
})
```

**Nunca** utilizar:

```js
confirm(...)
```

ni crear modales de eliminación independientes para cada vista.

### Tarjetas de producto

Para mostrar un producto individual utilizar siempre:

```blade
<x-producto-card :prod="$producto" />
```

No duplicar manualmente el HTML de una tarjeta de producto en catálogo, dashboard, inicio, relacionados u otras vistas.

### Modal de búsqueda

Para selectores y búsquedas masivas utilizar:

```blade
<x-modal-busqueda
    id="..."
    titulo="..."
    subtitulo="..."
    icono="..."
    placeholder="..."
/>
```

y el helper global:

```js
window.ModalBuscador
```

No duplicar la lógica visual/JS del buscador en cada formulario.

---

# 3. Tablas y listados

* No mostrar IDs técnicos como `#123` debajo del nombre salvo que se solicite explícitamente.
* Los estados deben utilizar badges tipo pill:

```html
rounded-full
```

* Estado activo: punto `w-1.5 h-1.5 rounded-full bg-emerald-500`.
* Estado inactivo: `bg-slate-400`.

---

# 4. Arquitectura y separación de responsabilidades

### Blade

Las vistas:

```text
resources/views/**/*.blade.php
```

deben encargarse principalmente de:

* HTML.
* Tailwind/CSS.
* Componentes.
* Renderizado.

**No consultar la BD desde Blade.**

Evitar:

```php
\App\Models\...
DB::table(...)
```

y consultas dentro de `@php`.

Tampoco colocar transformaciones complejas de datos en las vistas.

### Controllers

Los Controllers deben preparar los datos necesarios:

* Consultas.
* Filtrado.
* Mapeo.
* Formateo.
* Datos JSON para JavaScript.
* Marcas.
* Categorías.
* Atributos.
* Etc.

### Services

La lógica de negocio y cálculos reutilizables deben centralizarse en:

```text
app/Services/
```

Ejemplo:

```text
app/Services/EnvioService.php
```

No duplicar reglas de negocio entre Controllers, Livewire y Blade.

---

# 5. Modales y grandes cantidades de datos

Los selectores masivos de:

* Marcas.
* Categorías.
* Atributos.
* Zonas.
* Catálogos.

deben renderizar inicialmente **máximo 15 elementos**:

```text
porPagina: 15
```

No cargar miles de registros directamente en el DOM.

Para estructuras grandes, como miles de variantes, utilizar payload JSON:

```text
variantes_json
```

en lugar de enviar miles de variables individuales y arriesgar `max_input_vars`.

---

# 6. Vite, Tailwind y assets

**Nunca utilizar `cdn.tailwindcss.com` ni CDN para compilar CSS/JS en runtime.**

Todo debe utilizar Vite:

```blade
@vite(['resources/css/app.css', 'resources/js/app.js'])
```

Después de modificar `tailwind.config.js`, `@layer` o `@apply`:

```bash
npm run build
```

### Caché de assets

Mantener sincronizados:

* `public/.htaccess` → producción Apache.
* `server.php` → servidor PHP de desarrollo.

Política actual:

| Asset                           | Cache-Control |                       |
| ------------------------------- | ------------: | --------------------- |
| `/build/assets/*.js             |          css` | `31536000, immutable` |
| Fuentes                         |      `604800` |                       |
| `/storage/**`, `/uploads/**`    |       `86400` |                       |
| Imágenes/JS/CSS sin fingerprint |      `604800` |                       |

Al agregar nuevos tipos de assets, revisar ambos archivos.

---

# 7. Livewire y navegación SPA

Todo enlace interno del **storefront** debe utilizar:

```blade
wire:navigate
```

para evitar full page reload.

Excepciones:

* Panel admin.
* `target="_blank"`.
* Anclas `#` o `#seccion`.
* Login, registro y reset.
* Casos donde la navegación clásica sea intencional.

Si un enlace del drawer del carrito lleva a carrito/checkout, cerrar primero el drawer.

---

# 8. JavaScript con `wire:navigate`

Las páginas navegadas mediante `wire:navigate` **no deben depender de `DOMContentLoaded`** para ejecutar inicializaciones.

Preferir:

* IIFE.
* Inicialización inmediata.
* `dataset.inited`.
* Scope local para `const`/`let`.
* Funciones necesarias para `onclick` expuestas mediante `window`.
* Listeners globales registrados una sola vez.

Patrón de referencia:

```text
resources/views/cliente/perfil/datos.blade.php
```

### Pendientes conocidos

Revisar:

```text
resources/views/cliente/checkout/pago.blade.php
resources/views/cliente/checkout/confirmacion.blade.php
resources/views/components/toast-alert.blade.php
```

porque contienen inicializaciones basadas en `DOMContentLoaded`.

En `checkout/pago.blade.php`, el problema es crítico porque puede impedir el funcionamiento del formulario de transferencia.

### Galería de producto

`catalogo/detalle.blade.php` ya fue corregido.

Mantener:

* IIFE.
* Sin `const`/`let` globales.
* Init inmediata.
* Funciones inline expuestas mediante `window`.
* Listener `keydown` global único.

---

# 9. Rendimiento y consultas duplicadas

Evitar que varios componentes Livewire consulten los mismos datos independientemente.

### Carrito

`CarritoService` debe ser un **singleton** y utilizar memoización por petición mediante:

```text
carritosPorPeticion
```

`obtenerOCrearCarrito()` debe reutilizar el carrito ya resuelto durante la petición.

Las mutaciones deben invalidar la caché:

* `agregarProducto`
* `actualizarCantidad`
* `eliminarItem`
* `aplicarCupon`
* `removerCupon`
* `fusionarCarritos`
* `recalcularDescuentoCupon`

No realizar nuevamente `->load()` sobre relaciones que el servicio ya cargó.

Antes de cargar una relación adicional, utilizar `relationLoaded()`.

### Problema conocido

`CarritoDrawer::cargarCarrito()` puede generar una consulta adicional si solicita:

```php
items.producto.brand
```

pero dicha relación no fue cargada por `CarritoService`.

Preferir agregarla al eager loading del servicio o comprobar `relationLoaded()`.

---

# 10. Caché de Laravel

`config:cache` y `route:cache` son **solo para producción/despliegue**.

No ejecutarlos en desarrollo local.

Motivos:

* `config:cache` puede impedir que cambios en `.env` se apliquen hasta `config:clear`.
* `route:cache` no funciona con rutas basadas en closures.

`view:cache` puede utilizarse si es necesario.

---

# 11. Servidor de desarrollo

Cuando se modifique `server.php`, reiniciar:

```bash
php artisan serve
```

para asegurar que el servidor utilice el router actualizado.

Verificar las cabeceras con:

```bash
curl -I http://127.0.0.1:8000/...
```

---

# 12. Breeze / código legado

Estos archivos son restos de Laravel Breeze:

```text
layouts/app.blade.php
layouts/navigation.blade.php
```

Actualmente son utilizados únicamente por `profile/edit.blade.php`, sin una ruta activa relevante.

No afectan al rendimiento actual. Pueden eliminarse o migrarse durante una limpieza posterior.

---

# 13. Comentarios en el código

Los comentarios deben ser:

* Simples.
* Humanos.
* Descriptivos.
* Útiles para entender la sección.

Evitar comentarios excesivamente técnicos, largos o redundantes.

Ejemplo correcto:

```php
// Cargamos las categorías para mostrarlas en el selector.
```

No llenar el código de comentarios que simplemente repitan lo que ya hace el código.

---

# Prioridades

Al modificar el proyecto, respetar este orden:

1. **No romper la arquitectura MVC.**
2. **Reutilizar componentes y Services existentes.**
3. **Mantener la identidad visual global.**
4. **Evitar consultas y renders innecesarios.**
5. **Mantener `wire:navigate` en el storefront.**
6. **No romper JavaScript al navegar con Livewire.**
7. **Mantener Vite como sistema de assets.**
8. **Respetar los componentes globales de Toast, eliminación, búsqueda y productos.**
9. **No introducir consultas a BD ni lógica compleja en Blade.**
10. **No ejecutar configuraciones de producción durante desarrollo.**

Antes de crear una nueva solución, buscar primero si ya existe un **componente, helper, Service, patrón JavaScript o sistema global equivalente** y reutilizarlo.
