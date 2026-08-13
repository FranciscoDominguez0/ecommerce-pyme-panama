<div align="center">

# eCommerce PyME Panama

**Plataforma de comercio electronico para pequenas y medianas empresas panamenas**
Especializada en tecnologia, equipos informaticos, perifericos y servicios IT.

[![PHP](https://img.shields.io/badge/PHP-8.3%2B-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://www.php.net/)
[![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com/)
[![Livewire](https://img.shields.io/badge/Livewire-4.x-FB70A9?style=for-the-badge&logo=livewire&logoColor=white)](https://livewire.laravel.com/)
[![PostgreSQL](https://img.shields.io/badge/PostgreSQL-14%2B-336791?style=for-the-badge&logo=postgresql&logoColor=white)](https://www.postgresql.org/)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-3.x-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)](https://tailwindcss.com/)
[![Docker](https://img.shields.io/badge/Docker-Compose-2496ED?style=for-the-badge&logo=docker&logoColor=white)](https://www.docker.com/)

</div>

---

## Tabla de contenido

| Seccion | Descripcion |
|---|---|
| [Stack tecnologico](#stack-tecnologico) | Tecnologias y versiones utilizadas |
| [Requisitos del sistema](#requisitos-del-sistema) | Software necesario antes de instalar |
| [Instalacion paso a paso](#instalacion-paso-a-paso) | Guia detallada de instalacion |
| [Verificacion de la instalacion](#verificacion-de-la-instalacion) | URLs para confirmar que todo funciona |
| [Seeders y datos de catalogo](#seeders-y-datos-de-catalogo) | Carga de datos iniciales |
| [Pruebas automatizadas](#pruebas-automatizadas) | Ejecucion del suite de tests |
| [Comandos utiles](#comandos-utiles) | Referencia rapida de Artisan y npm |
| [Despliegue a produccion](#despliegue-a-produccion) | Checklist de despliegue |
| [Notas de seguridad](#notas-de-seguridad) | Buenas practicas de seguridad |
| [Solucion de problemas](#solucion-de-problemas) | Errores comunes y sus soluciones |

---

## Stack tecnologico

| Capa | Tecnologia | Version | Notas |
|---|---|---|---|
| **Backend** | Laravel (PHP) | 12.x | Requiere PHP ^8.3 |
| **Frontend reactivo** | Livewire | 4.x | SPA-like con `wire:navigate` |
| **Estilos** | Tailwind CSS | 3.x | Compilado con Vite, sin CDN en runtime |
| **Compilador de assets** | Vite | 8.x | Pipeline de build (JS + CSS) |
| **Base de datos** | PostgreSQL | 14+ | Imagen Docker: `postgres:16-alpine` |
| **Roles y permisos** | spatie/laravel-permission | 8.x | Roles: `super_admin`, `Admin`, `cliente` |
| **Generacion de PDF** | barryvdh/laravel-dompdf | 3.x | Facturas en PDF |
| **Exportacion Excel** | maatwebsite/excel | 3.x | Reportes de administracion |
| **Orquestacion** | Docker Compose | -- | `compose.yaml` incluido (opcional) |

---

## Requisitos del sistema

Asegurate de tener instalado todo lo siguiente antes de comenzar.

| Requisito | Version minima | Recomendada | Notas |
|---|---|---|---|
| **PHP** | 8.3 | 8.4 | Extensiones: `pdo_pgsql`, `pgsql`, `gd`, `intl`, `zip`, `mbstring`, `bcmath`, `pcntl`, `xml` |
| **Composer** | 2.x | Ultima estable | Gestor de dependencias PHP |
| **Node.js** | 20 LTS | Ultima LTS | Incluye npm |
| **npm** | 10.x | Incluida con Node | Gestor de paquetes JS |
| **PostgreSQL** | 14 | 16 | Motor de base de datos |
| **Git** | 2.x | Ultima estable | Control de versiones |

---

## Instalacion paso a paso

### Paso 1 — Clonar el repositorio

```bash
git clone <url-del-repositorio> ecommerce-pyme-panama
cd ecommerce-pyme-panama
```

---

### Paso 2 — Instalar dependencias de PHP

```bash
composer install
```

---

### Paso 3 — Configurar el archivo de entorno

Copia el archivo de ejemplo y ajusta los valores segun tu entorno local:

```bash
cp .env.example .env
```

Edita el `.env` con los siguientes valores clave:

| Variable | Valor de ejemplo | Descripcion |
|---|---|---|
| `APP_NAME` | `eCommerce PyME Panama` | Nombre de la aplicacion |
| `APP_ENV` | `local` | Entorno de ejecucion |
| `APP_URL` | `http://localhost:8000` | URL base (`php artisan serve`) |
| `APP_LOCALE` | `es` | Idioma de la aplicacion |
| `APP_TIMEZONE` | `America/Panama` | Zona horaria |
| `DB_CONNECTION` | `pgsql` | **Obligatorio:** PostgreSQL |
| `DB_HOST` | `127.0.0.1` | Host de PostgreSQL |
| `DB_PORT` | `5432` | Puerto de PostgreSQL |
| `DB_DATABASE` | `ecommerce_pyme_panama` | Base de datos de desarrollo |
| `DB_USERNAME` | `postgres` | Usuario de PostgreSQL |
| `DB_PASSWORD` | `tu_contrasena_segura` | Contrasena de PostgreSQL |

> **Importante:** Esta aplicacion requiere PostgreSQL obligatoriamente. No uses el valor `sqlite` que trae el `.env.example` por defecto.

---

### Paso 4 — Generar la clave de la aplicacion

```bash
php artisan key:generate
```

---

### Paso 5 — Crear la base de datos

**Opcion A — Desde la linea de comandos (psql tools):**

```bash
createdb ecommerce_pyme_panama
```

**Opcion B — Desde la consola de PostgreSQL:**

```sql
CREATE DATABASE ecommerce_pyme_panama;
```

---

### Paso 6 — Ejecutar las migraciones

```bash
php artisan migrate
```

---

### Paso 7 — Crear el enlace de almacenamiento publico

Necesario para servir las imagenes de productos subidas por el administrador:

```bash
php artisan storage:link
```

---

### Paso 8 — Instalar dependencias JS y compilar assets

```bash
npm install
```

| Modo | Comando | Cuando usarlo |
|---|---|---|
| **Desarrollo** (watch en tiempo real) | `npm run dev` | Mientras desarrollas |
| **Produccion** (bundle optimizado) | `npm run build` | Antes de subir a produccion |

> **Advertencia:** Nunca uses el CDN de Tailwind en runtime (`cdn.tailwindcss.com`). Todo el CSS/JS debe compilarse con Vite.

---

### Paso 9 — Sembrar los datos base

Ejecuta los seeders en el siguiente orden:

```bash
# Roles, permisos, 63 marcas y atributos de variante
php artisan db:seed

# Usuario administrador (admin@example.com)
php artisan db:seed --class=RolesPermisosSeeder

# 43 categorias y subcategorias en espanol
php artisan db:seed --class=CategoriaSeeder

# Zonas de envio por provincia de Panama
php artisan db:seed --class=ZonaEnvioSeeder
```

---

### Paso 10 — Credenciales de administrador

El seeder `RolesPermisosSeeder` crea el usuario inicial:

| Campo | Valor |
|---|---|
| **Correo electronico** | `admin@example.com` |
| **Contrasena** | `Admin1234!` |
| **Rol** | `super_admin` |
| **Acceso** | `http://localhost:8000/admin/dashboard` |

> Cambia la contrasena inmediatamente despues del primer ingreso. En produccion, crea usuarios propios y elimina o deshabilita este usuario generico.

---

### Paso 11 — Iniciar el servidor de desarrollo

```bash
php artisan serve
```

El proyecto incluye un `server.php` personalizado que aplica cabeceras de cache para los assets estaticos en desarrollo (mismo comportamiento que el `.htaccess` de Apache en produccion).

> Si el servidor ya estaba corriendo antes de la instalacion, reinicialo para que tome el `server.php` actualizado.

---

## Verificacion de la instalacion

Una vez iniciado el servidor, verifica que las siguientes URLs respondan correctamente:

| Seccion | URL | Acceso requerido |
|---|---|---|
| **Tienda — Inicio** | `http://localhost:8000/` | Publico |
| **Catalogo de productos** | `http://localhost:8000/catalogo` | Publico |
| **Inicio de sesion** | `http://localhost:8000/login` | Publico |
| **Registro de cliente** | `http://localhost:8000/register` | Publico |
| **Panel de administracion** | `http://localhost:8000/admin/dashboard` | Roles `Admin` / `super_admin` |
| **Mi cuenta (cliente)** | `http://localhost:8000/mi-cuenta` | Usuario autenticado |

---

## Seeders y datos de catalogo

### Seeders de instalacion

| Seeder | Que genera | Comando |
|---|---|---|
| `DatabaseSeeder` | Roles (`Admin`, `super_admin`, `cliente`), permisos, 63 marcas, atributos de variante | `php artisan db:seed` |
| `RolesPermisosSeeder` | Usuario administrador `admin@example.com` con rol `super_admin` | `php artisan db:seed --class=RolesPermisosSeeder` |
| `CategoriaSeeder` | 43 categorias y subcategorias (nombres en espanol, slugs canonicos) | `php artisan db:seed --class=CategoriaSeeder` |
| `ZonaEnvioSeeder` | Zonas de envio por provincia de Panama | `php artisan db:seed --class=ZonaEnvioSeeder` |

### CatalogoDemoSeeder — Solo para testing

> **Advertencia:** Este seeder **solo puede ejecutarse contra `ecommerce_test`**. Tiene un guard interno que aborta si la conexion activa no es esa.

```bash
php artisan db:seed --class=CatalogoDemoSeeder --env=testing
```

**Datos generados:**

| Dato | Cantidad |
|---|---|
| **Marcas** | 63 |
| **Categorias** (raiz + subcategorias) | 43 |
| **Productos** (bienes fisicos) | 1 051 |
| **Servicios informaticos** | 24 |
| **Variantes de producto** | ~832 (en ~22% de los productos) |

| Aspecto | Comportamiento |
|---|---|
| Nombres de categorias y productos | Listas curadas en espanol real (sin Faker ni Lorem Ipsum) |
| Slug de categorias | Siempre derivado de `Str::slug($nombre)` |
| Servicios informaticos | `stock = 999` (sin inventario), `brand_id = null`, `aplica_itbms = true` |
| Stock de productos con variantes | Igual a la suma del stock de sus variantes |
| Imagenes | No se crean filas en `imagenes_producto`; el storefront muestra un placeholder |
| Re-ejecucion | Idempotente: `firstOrCreate`/`updateOrCreate` por slug o SKU |

---

## Pruebas automatizadas

Los tests corren contra una base de datos dedicada `ecommerce_test`, protegida con doble candado:

- `phpunit.xml` fija explicitamente todas las variables `DB_*` a `ecommerce_test`.
- `tests/TestCase.php` valida en `setUp()` que la conexion activa sea `ecommerce_test` antes de cualquier `migrate:fresh`.

```bash
# Crear la base de datos de tests (una sola vez)
createdb ecommerce_test

# Ejecutar el suite completo
php artisan test --env=testing
```

> **Advertencia:** Nunca ejecutes `migrate:fresh`, `migrate:refresh` o `db:wipe` sin confirmar antes el `DB_DATABASE` activo con `php artisan about`. Un error puede borrar el catalogo de desarrollo de forma irreversible.

---

## Comandos utiles

### Artisan (backend)

| Comando | Descripcion |
|---|---|
| `php artisan serve` | Servidor de desarrollo con cabeceras de cache |
| `php artisan migrate` | Ejecuta las migraciones pendientes |
| `php artisan migrate:status` | Lista el estado de todas las migraciones |
| `php artisan db:seed` | Ejecuta `DatabaseSeeder` (roles, marcas, atributos) |
| `php artisan storage:link` | Enlaza `storage/app/public` con `public/storage` |
| `php artisan key:generate` | Genera `APP_KEY` en el `.env` |
| `php artisan tinker` | Consola interactiva de Laravel |
| `php artisan test --env=testing` | Suite de pruebas automatizadas |
| `php artisan queue:listen` | Procesa trabajos en cola (`QUEUE_CONNECTION=database`) |
| `php artisan about` | Muestra la configuracion activa (verifica `DB_DATABASE`) |

### npm (frontend)

| Comando | Descripcion |
|---|---|
| `npm install` | Instala las dependencias de JavaScript |
| `npm run dev` | Compilador de assets en modo watch (desarrollo) |
| `npm run build` | Compilacion optimizada de assets para produccion |

---

## Despliegue a produccion

### Checklist de despliegue

```bash
# ── 1. Dependencias ─────────────────────────────────────────────
composer install --no-dev --optimize-autoloader
npm ci && npm run build

# ── 2. Base de datos ─────────────────────────────────────────────
php artisan migrate --force

# ── 3. Cache (solo en produccion) ────────────────────────────────
php artisan config:cache
php artisan route:cache
php artisan view:cache

# ── 4. Storage ───────────────────────────────────────────────────
php artisan storage:link
```

### Notas importantes de produccion

| Aspecto | Detalle |
|---|---|
| **`config:cache`** | Cifra los valores de `.env` dentro del cache. Despues de cambiar `.env`, ejecuta `php artisan config:clear && php artisan config:cache`. No lo uses en desarrollo local. |
| **`route:cache`** | Requiere que todas las rutas sean serializables (sin closures en `routes/*.php`). Se invalida con `php artisan route:clear`. |
| **Cache de assets** | El `.htaccess` aplica `Cache-Control: immutable` (1 ano) a `/build/assets/*.js|css`, 1 semana a fuentes y 1 dia a archivos bajo `/storage` y `/uploads`. |
| **Pasarelas de pago** | Stripe y Yappy son simulaciones puras. Integra los SDKs reales antes de ir a produccion. |
| **Roles y permisos** | Nunca uses IDs numericos de roles. Usa siempre `assignRole('cliente')` o `hasRole('super_admin')`. Los IDs no son estables entre entornos. |

### Despliegue con Docker (opcional)

El proyecto incluye `Dockerfile` y `compose.yaml` (PostgreSQL 16 + PHP-FPM + Nginx):

```bash
# 1. Copiar el archivo de entorno Docker
cp .env.docker .env

# 2. Editar APP_KEY, DB_PASSWORD y los valores que correspondan

# 3. Construir y levantar los contenedores
docker compose up --build -d
```

---

## Notas de seguridad

| Regla | Descripcion |
|---|---|
| **No uses IDs numericos de roles** | Los IDs se auto-generan y no son estables entre entornos. Usa siempre `assignRole('cliente')` o `hasRole('super_admin')`. |
| **RolesSeeder es la fuente de verdad** | Para roles (`Admin`, `super_admin`, `cliente`) y permisos. Ejecutalo en toda BD nueva antes de usar funciones basadas en roles. |
| **CatalogoDemoSeeder esta blindado** | Solo puede ejecutarse contra `ecommerce_test`. Nunca lo ejecutes contra la BD de desarrollo ni de produccion. |
| **No versiones `.env`** | El repositorio solo incluye `.env.example` y `.env.docker`. Nunca commitees `.env` ni `.env.testing`. |
| **Cambia las credenciales por defecto** | El usuario `admin@example.com` / `Admin1234!` es solo para arranque. Cambialo o eliminalo en produccion. |

---

## Solucion de problemas

| Sintoma | Causa probable | Solucion |
|---|---|---|
| `PDOException: could not find driver` | Falta la extension `pdo_pgsql` | Instala/habilita `pdo_pgsql` y `pgsql` en PHP |
| Error de conexion a PostgreSQL | Credenciales incorrectas en `.env` | Verifica `DB_DATABASE`, `DB_USERNAME` y `DB_PASSWORD`; crea la base de datos |
| Error 500 o `APP_KEY` vacio | Clave de aplicacion no generada | Ejecuta `php artisan key:generate` |
| Estilos sin cargar / pagina en blanco | Assets no compilados | Ejecuta `npm run build` (o `npm run dev` en desarrollo) |
| Imagenes de productos no visibles | Falta el enlace de storage | Ejecuta `php artisan storage:link` |
| `route:cache` lanza error con closures | Rutas no serializables | Usa `route:cache` solo en produccion; elimina closures de `routes/*.php` |
| El catalogo demo se niega a ejecutarse | BD activa no es `ecommerce_test` | Ejecuta con `--env=testing` |
| Roles/permisos ausentes tras resetear la BD | No se corrio `RolesSeeder` | Ejecuta `php artisan db:seed` y luego `--class=RolesPermisosSeeder` |
| Cambios en `.env` no se reflejan | `config:cache` activo en desarrollo | Ejecuta `php artisan config:clear` |
| Sesion o cache inconsistente | Archivos de cache desactualizados | Ejecuta `php artisan cache:clear && php artisan config:clear` |

---

<div align="center">

**eCommerce PyME Panama** -- Hecho en Panama

Laravel 12 -- Livewire 4 -- PostgreSQL -- Tailwind CSS -- Vite

</div>
