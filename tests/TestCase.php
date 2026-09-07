<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    public function createApplication()
    {
        $app = parent::createApplication();
        if ($app['config']->get('database.default') !== 'sqlite'
            || $app['config']->get('database.connections.sqlite.database') !== ':memory:') {
            throw new \RuntimeException('Tests require an isolated in-memory database.');
        }
        foreach (array_keys($app['config']->get('database.connections')) as $name) {
            $app['config']->set("database.connections.$name", $app['config']->get('database.connections.sqlite'));
        }
        // Explicit legacy connection names share the same isolated database.
        foreach (['mysql', 'sqlsrv', 'sqlsrv2'] as $name) {
            $app['db']->connection($name)->setPdo($app['db']->connection('sqlite')->getPdo());
        }
        $app['config']->set('logging.channels.audit', ['driver' => 'null']);
        return $app;
    }
}
