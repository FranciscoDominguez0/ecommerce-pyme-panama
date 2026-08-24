<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
$users = \App\Models\Usuario::with('roles')->get();
foreach($users as $user) {
    echo $user->email . ": " . implode(',', $user->roles->pluck('name')->toArray()) . "\n";
}
