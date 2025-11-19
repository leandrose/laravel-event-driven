<?php

namespace LeandroSe\LaravelEventDriven;

use Illuminate\Contracts\Queue\Factory as QueueFactoryContract;
use Illuminate\Support\ServiceProvider as LaravelServiceProvider;
use LeandroSe\LaravelEventDriven\Console\Commands\EventDrivenCommand;
use LeandroSe\LaravelEventDriven\Console\Commands\OutboxEventWorkerCommand;
use LeandroSe\LaravelEventDriven\Console\Commands\SupervisorCommand;
use LeandroSe\LaravelEventDriven\Console\Commands\WorkerCommand;

/**
 * Laravel service provider that wires the package into an application.
 *
 * It replaces the default event dispatcher, exposes the connector manager, and registers migrations
 * as well as artisan commands required by the outbox workflow.
 */
class ServiceProvider extends LaravelServiceProvider
{

    /**
     * Register bindings and singletons used by the package.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('events', function ($app) {
            return (new Dispatcher($app))->setQueueResolver(function () use ($app) {
                return $app->make(QueueFactoryContract::class);
            })->setTransactionManagerResolver(function () use ($app) {
                return $app->bound('db.transactions')
                    ? $app->make('db.transactions')
                    : null;
            });
        });

        $this->app->singleton('event-driven', function () {
            return new EventDrivenManager($this->app);
        });
        $this->app->bind(EventDrivenManager::class, function () {
            return $this->app['event-driven'];
        });
        $this->app->singleton('event-driven.driver', function () {
            return $this->app['event-driven']->connection();
        });
        $this->app->bind(ConnectorContract::class, function () {
            return $this->app['event-driven']->connection();
        });
        $this->mergeConfigFrom(__DIR__ . '/../config/event-driven.php', 'event-driven');
    }

    /**
     * Bootstrap package resources and console tooling.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->publishes([
            __DIR__ . '/../config/event-driven.php' => config_path('event-driven.php'),
        ]);

        $this->bootForConsole();
    }

    /**
     * Register artisan commands when running in the console.
     *
     * @return void
     */
    private function bootForConsole()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                EventDrivenCommand::class,
                SupervisorCommand::class,
                WorkerCommand::class,
                OutboxEventWorkerCommand::class,
            ]);
        }
    }
}
