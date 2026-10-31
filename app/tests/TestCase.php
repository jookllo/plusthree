<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    /**
     * Fail before any database trait runs, not after.
     *
     * RefreshDatabase rebuilds tables during setUpTraits(), so a guard placed
     * inside a test method would run too late.
     */
    protected function setUpTraits()
    {
        $this->guardTestDatabase();

        return parent::setUpTraits();
    }

    private function guardTestDatabase(): void
    {
        $connection = config('database.default');
        $driver = config("database.connections.{$connection}.driver");
        $database = config("database.connections.{$connection}.database");

        if ($driver !== 'mysql' || $database !== 'plusthree_testing') {
            throw new RuntimeException(sprintf(
                'Refusing to run tests against driver [%s] database [%s]. '
                .'Tests must use the isolated MySQL database [plusthree_testing]; '
                .'check app/.env.testing and phpunit.xml.',
                (string) $driver,
                (string) $database
            ));
        }
    }
}
