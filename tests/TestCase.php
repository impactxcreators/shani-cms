<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // SAFETY: tests must never run against the live database.
        // If config was cached with production settings, refuse to continue.
        $connection = config('database.default');
        $database = config("database.connections.{$connection}.database");

        if ($connection !== 'sqlite' || $database !== ':memory:') {
            $this->fail(
                "Refusing to run tests: expected sqlite :memory:, got [{$connection}:{$database}]. "
                . "Run 'php artisan config:clear' before testing."
            );
        }
    }
}
