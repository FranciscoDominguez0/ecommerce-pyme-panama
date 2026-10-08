<?php
require __DIR__ . "/vendor/autoload.php";
$app = require_once __DIR__ . "/bootstrap/app.php";
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$p = App\Models\Producto::first();
var_dump($p->requiere_envio);
var_dump($p->requiere_envio ?? true);
var_dump($p?->requiere_envio ?? true);
