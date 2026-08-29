<?php

namespace Tests\Feature\Commands;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DecreaseStockCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_rejects_execution_when_environment_is_not_testing()
    {
        $this->app->detectEnvironment(fn() => 'local');

        DB::flushQueryLog();
        DB::enableQueryLog();

        $this->artisan('test:decrease-stock', [
            'user_id' => 999,
            'product_id' => 999,
            'quantity' => '1.000',
        ])
        ->expectsOutput('Este comando está restringido exclusivamente al entorno de pruebas.')
        ->assertExitCode(1);

        $queries = DB::getQueryLog();
        $tableQueries = array_filter($queries, function ($q) {
            return str_contains($q['query'], 'users') || str_contains($q['query'], 'products');
        });

        $this->assertEmpty($tableQueries, 'No debe realizar consultas a las tablas de usuarios ni productos cuando falla el entorno.');
    }

    public function test_command_rejects_execution_when_database_is_not_lubricantes_testing()
    {
        $defaultConn = config('database.default');
        config(["database.connections.{$defaultConn}.database" => 'other_database']);
        DB::purge();

        DB::flushQueryLog();
        DB::enableQueryLog();

        $this->artisan('test:decrease-stock', [
            'user_id' => 999,
            'product_id' => 999,
            'quantity' => '1.000',
        ])
        ->expectsOutput('Este comando está restringido exclusivamente al entorno de pruebas.')
        ->assertExitCode(1);

        $queries = DB::getQueryLog();
        $tableQueries = array_filter($queries, function ($q) {
            return str_contains($q['query'], 'users') || str_contains($q['query'], 'products');
        });

        $this->assertEmpty($tableQueries, 'No debe realizar consultas a las tablas de usuarios ni productos cuando falla la base activa.');

        config(["database.connections.{$defaultConn}.database" => 'lubricantes_testing']);
        DB::purge();
    }
}
