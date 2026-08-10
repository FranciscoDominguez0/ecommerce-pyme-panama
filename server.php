<?php

/**
 * Router del servidor de desarrollo (php artisan serve).
 * Replica el router de Laravel y añade cabeceras HTTP de caché para los
 * assets estáticos, de modo que el navegador no los vuelva a descargar en
 * cada navegación (mismo comportamiento que public/.htaccess en producción).
 */

$publicPath = getcwd();

$uri = urldecode(
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? ''
);

// Si es un archivo estático existente, lo servimos nosotros mismos para poder
// inyectar las cabeceras de caché (el servidor PHP no las añade por defecto).
if ($uri !== '/' && file_exists($publicPath.$uri) && !is_dir($publicPath.$uri)) {
    $file = $publicPath.$uri;
    $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));

    $contentTypes = [
        'css' => 'text/css; charset=UTF-8',
        'js' => 'application/javascript',
        'json' => 'application/json',
        'svg' => 'image/svg+xml',
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif' => 'image/gif',
        'webp' => 'image/webp',
        'ico' => 'image/x-icon',
        'woff2' => 'font/woff2',
        'woff' => 'font/woff',
        'ttf' => 'font/ttf',
        'otf' => 'font/otf',
    ];

    if (preg_match('#^/build/assets/.*\.(js|css)$#', $uri)) {
        // Assets de Vite con hash: cache largo e inmutable
        $cacheControl = 'public, max-age=31536000, immutable';
    } elseif (preg_match('#\.(woff2?|ttf|otf|eot)(\?.*)?$#', $uri)) {
        // Fuentes locales: 1 semana
        $cacheControl = 'public, max-age=604800';
    } elseif (preg_match('#^/(storage|uploads)/.*\.(svg|png|jpe?g|gif|webp|ico)$#', $uri)) {
        // Contenido subido por usuarios: 1 día (puede cambiar)
        $cacheControl = 'public, max-age=86400';
    } elseif (preg_match('#\.(svg|png|jpe?g|gif|webp|ico)$#', $uri)) {
        // Imágenes/íconos estáticos: 1 semana
        $cacheControl = 'public, max-age=604800';
    } elseif (preg_match('#\.(js|css)$#', $uri)) {
        // JS/CSS no fingerprinted: 1 semana
        $cacheControl = 'public, max-age=604800';
    } else {
        return false;
    }

    header('Cache-Control: '.$cacheControl);
    if (isset($contentTypes[$extension])) {
        header('Content-Type: '.$contentTypes[$extension]);
    }
    header('Content-Length: '.filesize($file));
    readfile($file);

    return true;
}

$formattedDateTime = date('D M j H:i:s Y');

$requestMethod = $_SERVER['REQUEST_METHOD'];
$remoteAddress = $_SERVER['REMOTE_ADDR'].':'.$_SERVER['REMOTE_PORT'];

file_put_contents('php://stdout', "[$formattedDateTime] $remoteAddress [$requestMethod] URI: $uri\n");

require_once $publicPath.'/index.php';