<?php

namespace Tests\Feature\Commands;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ConfirmSaleCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_confirm_sale_command_fails_if_env_is_not_testing()
    {
        $this->app->detectEnvironment(fn() => 'local');

        DB::flushQueryLog();
        DB::enableQueryLog();

        $this->artisan('test:confirm-sale', [
            'user_id' => 999,
            'sale_id' => 999,
        ])
        ->expectsOutput('Este comando está restringido exclusivamente al entorno de pruebas.')
        ->assertExitCode(1);

        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        $tableQueries = array_filter($queries, function ($q) {
            return str_contains($q['query'], 'users') || str_contains($q['query'], 'sales');
        });

        $this->assertEmpty($tableQueries, 'No debe realizar consultas a las tablas de usuarios ni ventas cuando falla la barrera de entorno.');
    }

    public function test_confirm_sale_command_fails_if_database_is_not_lubricantes_testing()
    {
        $defaultConn = config('database.default');
        config(["database.connections.{$defaultConn}.database" => 'other_database']);
        DB::purge();

        DB::flushQueryLog();
        DB::enableQueryLog();

        $this->artisan('test:confirm-sale', [
            'user_id' => 999,
            'sale_id' => 999,
        ])
        ->expectsOutput('Este comando está restringido exclusivamente al entorno de pruebas.')
        ->assertExitCode(1);

        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        $tableQueries = array_filter($queries, function ($q) {
            return str_contains($q['query'], 'users') || str_contains($q['query'], 'sales');
        });

        $this->assertEmpty($tableQueries, 'No debe realizar consultas a las tablas de usuarios ni ventas cuando falla la barrera de base de datos.');

        config(["database.connections.{$defaultConn}.database" => 'lubricantes_testing']);
        DB::purge();
    }
}
