<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Nombre exacto de la base de datos dedicada a los tests.
     * Se verifica ANTES de que RefreshDatabase ejecute migrate:fresh, para que un
     * error de configuración falle de forma ruidosa en lugar de borrar la BD de
     * desarrollo (ecommerce_pyme_panama). Ver también phpunit.xml (bloqueo DB_*).
     */
    protected const BD_DE_TEST = 'ecommerce_test';

    protected function setUp(): void
    {
        $this->refreshApplication();

        $bdActiva = config('database.connections.' . config('database.default') . '.database');

        if ($bdActiva !== static::BD_DE_TEST) {
            throw new \RuntimeException(
                "Los tests SOLO pueden ejecutarse contra la base 'ecommerce_test' (conexión activa: '{$bdActiva}'). "
                . "Revisa .env.testing / phpunit.xml. Nunca ejecutes migrate:fresh, migrate:refresh o db:wipe "
                . "contra la base de desarrollo ecommerce_pyme_panama."
            );
        }

        parent::setUp();
    }
}
