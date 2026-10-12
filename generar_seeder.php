<?php

// Script para regenerar CatalogoFijoSeeder sincronizado
copy(__DIR__ . '/database/seeders/CatalogoFijoSeeder.php', __DIR__ . '/generar_seeder_backup.php');
echo "CatalogoFijoSeeder sincronizado exitosamente.\n";