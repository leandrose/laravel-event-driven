<?php

namespace LeandroSe\LaravelEventDriven\Tests;

use LeandroSe\LaravelEventDriven\ServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{

    protected function getPackageProviders($app)
    {
        return (ServiceProvider::class);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        $this->app['config']['event-driven.default'] = 'fake';
    }
}