<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $databaseName = \Illuminate\Support\Facades\DB::connection()->getDatabaseName();
        if ($databaseName !== 'lubricantes_testing') {
            throw new \RuntimeException("Base de pruebas insegura: {$databaseName}");
        }
        if ($databaseName === 'lubricantes') {
            throw new \RuntimeException('Ejecutando pruebas contra la base de datos de desarrollo');
        }
    }
}
