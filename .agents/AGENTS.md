# Normas de Desarrollo y Convenciones del Proyecto — PayMe Panamá

## 1. Tipografía Oficial del Sistema
- **Fuente Oficial**: `Plus Jakarta Sans` (`font-sans`), configurada globalmente en `resources/views/layouts/admin.blade.php`.
- **Regla**: NO cambiar ni importar fuentes secundarias en vistas individuales. Mantener la clase `font-sans` y la jerarquía de títulos del layout.

## 2. Sistema Global de Notificaciones y Alertas (Toast)
- **Componente Oficial**: `<x-toast-alert />` (definido en `resources/views/components/toast-alert.blade.php` e incluido al final de `resources/views/layouts/admin.blade.php`).
- **Regla**: NO agregar bloques de alerta inline `@if(session('success'))` ni `@if(session('error'))` dentro de las vistas Blade hijas. El layout captura automáticamente las llaves `session('success')`, `session('toast_success')`, `session('error')`, `session('warning')` y `session('info')` mostrando una notificación flotante estilo Toast. Agregar alertas inline causará mensajes duplicados en pantalla.

## 3. Identidad Visual de Marca y Colores
- **Color Accent Principal**: `emerald-600` / `#059669` ("Canal Emerald") para botones primarios, badges activos e indicadores de crecimiento.
- **Superficie y Fondos**: `#F8FAFC` (`slate-50`), bordes `#E5E7EB` (`slate-200`).
- **Sidebar**: Dark Slate `#1F2937` con texto `slate-300` e indicadores activos `emerald-400`.
- **Breadcrumb en TopBar**: `Panel` > `[Módulo]` > `[Acción (Editar/Nuevo/Index)]`. El TopNavBar permanece siempre fijo arriba (`sticky top-0 z-40 backdrop-blur-md`).

## 4. Estructura de Tablas y Listados
- **Limpieza de filas**: No mostrar IDs técnicos `#123` debajo del nombre del registro en las celdas principales salvo que sea explícitamente solicitado.
- **Badges de estado**: Usar estilo redondeado pill (`rounded-full`) con punto de color (`w-1.5 h-1.5 rounded-full bg-emerald-500` para activo / `bg-slate-400` para inactivo).

## 5. Módulos y Lógica de Negocio Centralizada
- **Servicios**: Toda la lógica de cálculo de tarifas o reglas de negocio debe estar centralizada en clases Service dentro de `app/Services/` (ejemplo: `EnvioService.php`).

## 6. Rendimiento en Modales y Listados Masivos (Regla de 15 Items & JSON Payload)
- **Límite de Renderizado (15 Items)**: Todo modal de selección masiva o selector de catálogo (Marcas, Categorías, Atributos Principales, Zonas) DEBE limitar el renderizado inicial del DOM a un máximo de 15 elementos (`porPagina: 15`). Esto garantiza búsquedas instantáneas y previene lentitud al manejar miles de registros.
- **Componente Oficial de Selección**: Utilizar el componente reutilizable `<x-modal-busqueda id="..." titulo="..." subtitulo="..." icono="..." placeholder="..." />` integrado con el helper global `window.ModalBuscador` para no duplicar lógica visual ni JavaScript entre formularios.
- **Serialización en JSON**: Para enviar estructuras de datos masivas (como matrices de 10,000 variantes), serializar el estado en un payload JSON (`variantes_json`) para evitar sobrepasar la restricción `max_input_vars` de PHP.

## 7. Arquitectura MVC y Separación de Responsabilidades
- **Prohibido Consultar la BD desde Vistas Blade**: Las vistas Blade (`resources/views/**/*.blade.php`) NO deben ejecutar consultas a la Base de Datos (`\App\Models\...`, `DB::table(...)`, helpers de BD) ni procesar transformaciones complejas de datos dentro de bloques `@php`.
- **Responsabilidad del Controlador**: Toda consulta a la BD, mapeo, filtrado y formateo de colecciones (JSON para JS, datos de marcas, categorías, atributos, etc.) DEBE ser procesado en la clase Controller correspondiente antes de pasar los datos a la vista.
- **Vistas Limpias**: Las vistas Blade deben permanecer exclusivamente enfocadas en la estructura HTML, estilos visuales y renderizado de componentes.

## 8. Sistema Global de Confirmación Defensiva para Eliminación
- **Componente Oficial**: `<x-modal-eliminar />` (definido en `resources/views/components/modal-eliminar.blade.php` e incluido automáticamente en `resources/views/layouts/admin.blade.php`).
- **Helper Global en JS**: `window.ModalEliminar.abrir(url, nombre, extra)` o con objeto `window.ModalEliminar.abrir({ url, nombre, extra, titulo, mensaje })`.
- **Regla**: NO usar diálogos nativos del navegador (`confirm(...)`) ni crear modales o formularios de eliminación ad-hoc repetidos en cada vista. Cualquier botón de acción "Eliminar" en tablas, listados o tarjetas de gestión administrativa debe invocar directamente `window.ModalEliminar.abrir(url, nombre, extra)`.

## 9. Componente Centralizado para Tarjetas de Producto
- **Componente Oficial**: `<x-producto-card :prod="$producto" />` (definido en `resources/views/components/producto-card.blade.php`).
- **Regla**: Cualquier vista que necesite mostrar una tarjeta de producto individual (catálogo, dashboard, inicio, relacionados, etc.) DEBE utilizar este componente. NO recrear manualmente la estructura HTML de las tarjetas de producto en otras vistas.

## 10. Lenguaje y Estilo de Comentarios
- **Regla**: Los comentarios en el código (especialmente en vistas de cliente) deben estar escritos en un lenguaje sencillo y "humano", como un técnico explicando de forma simple.
- **Evitar**: No usar lenguaje excesivamente técnico ni llenar el código con comentarios innecesarios o redundantes. Mantenerlos limpios y descriptivos de la sección visual que representan.


