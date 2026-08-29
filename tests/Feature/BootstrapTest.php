<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class BootstrapTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Verifica que la aplicación inicia y la página base responde correctamente.
     */
    public function test_application_boots_and_base_page_returns_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Lubricantes «El Cisne»');
    }

    /**
     * Verifica que la configuración principal está disponible y con los valores esperados.
     */
    public function test_main_configuration_is_available(): void
    {
        $this->assertEquals('es', Config::get('app.locale'));
        $this->assertEquals('es', Config::get('app.fallback_locale'));
        $this->assertEquals('USD', Config::get('app.currency'));
        $this->assertNotEmpty(Config::get('app.name'));
    }

    /**
     * Verifica que la zona horaria configurada es America/Guayaquil.
     */
    public function test_configured_timezone_is_america_guayaquil(): void
    {
        $this->assertEquals('America/Guayaquil', Config::get('app.timezone'));
        $this->assertEquals('America/Guayaquil', date_default_timezone_get());
    }

    /**
     * Verifica que el entorno de pruebas no apunta a la misma base de desarrollo.
     */
    public function test_testing_environment_does_not_point_to_dev_database(): void
    {
        $devDatabase = 'lubricantes';
        $currentTestingDatabase = DB::connection()->getDatabaseName();

        $this->assertEquals('testing', app()->environment());
        $this->assertNotEquals($devDatabase, $currentTestingDatabase);
        $this->assertEquals('lubricantes_testing', $currentTestingDatabase);
    }
}
