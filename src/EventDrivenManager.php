<?php

namespace LeandroSe\LaravelEventDriven;

use Illuminate\Contracts\Container\Container;

/**
 * Resolves messaging connectors declared in the configuration and caches active instances.
 *
 * The manager exposes lazy-loaded connectors by name as well as the default connector defined in config.
 */
class EventDrivenManager
{

    /**
     * Cached connector instances keyed by connection name.
     *
     * @var array<string, ConnectorContract>
     */
    protected array $connectors = [];

    /**
     * @param Container $app Laravel service container used to resolve configuration and dependencies.
     */
    public function __construct(protected Container $app)
    {
    }

    /**
     * Register a listener for a given event.
     * @param string|array $eventName Event name.
     * @param string|array $listens Listener class name.
     * @return void
     */
    public function listen(string|array $eventName, string|array $listens): void
    {
        $eventName = is_string($eventName) ? [$eventName] : $eventName;
        $listens = array_filter(is_string($listens) ? [$listens] : $listens, fn($item) => class_exists($item));
        if (!count($listens)) {
            return;
        }

        foreach ($eventName as $event) {
            if (empty(config('event-driven.listeners.' . $event))) {
                config(['event-driven.listeners.' . $event => $listens]);
            } else {
                $listeners = config('event-driven.listeners.' . $event);
                $listeners = array_unique(array_merge($listeners, $listens));
                config(['event-driven.listeners.' . $event => $listeners]);
            }
        }
    }

    /**
     * Retrieve a connector by name or fall back to the default connection.
     *
     * @param string|null $name Optional connection name defined under `event-driven.connections`.
     * @return ConnectorContract
     * @throws ConnectorNotFoundException When the requested driver cannot be built.
     *
     * @see EventDrivenManager::createKafkaDriver()
     * @see EventDrivenManager::createNullDriver()
     */
    public function connection(string $name = null): ConnectorContract
    {
        $name = $name ?? $this->getDefaultDriver();

        return $this->connectors[$name] ??= $this->resolve($name);
    }

    /**
     * Instantiate the connector configured for the given connection name.
     *
     * @param string $name Connection name.
     * @return ConnectorContract
     *
     * @throws ConnectorNotFoundException When no factory method exists for the requested driver.
     */
    public function resolve(string $name): ConnectorContract
    {
        $config = $this->getConfig($name);
        $method = 'create' . ucfirst($config['driver']) . 'Driver';
        if (method_exists($this, $method)) {
            return $this->{$method}($config);
        } else {
            throw new ConnectorNotFoundException("Driver $name not found.");
        }
    }

    /**
     * Retrieve the configuration array for the given connection.
     *
     * @param string $name Connection name.
     * @return array<string, mixed>
     * @throws ConnectorNotFoundException
     */
    public function getConfig(string $name): array
    {
        $config = $this->app['config']["event-driven.connections.$name"];
        if (empty($config)) {
            throw new ConnectorNotFoundException("Driver $name not found.");
        }
        return $config;
    }

    /**
     * Resolve the default connection name defined in configuration.
     *
     * @return string
     */
    public function getDefaultDriver(): string
    {
        return $this->app['config']['event-driven.default'] ?? 'null';
    }

    /**
     * Factory for the Kafka connector.
     *
     * @param array<string, mixed> $options Connector configuration.
     * @return ConnectorContract
     */
    public function createKafkaDriver(array $options): ConnectorContract
    {
        return new KafkaConnector($this->app, $options['bootstrap_servers'] ?? 'localhost:9092');
    }

    /**
     * Factory for the fake connector used in tests.
     *
     * @param array<string, mixed> $options Connector configuration (ignored).
     * @return ConnectorContract
     */
    public function createFakeDriver(array $options): ConnectorContract
    {
        return new FakeConnector();
    }

    /**
     * Factory for the null connector that discards messages.
     *
     * @param array<string, mixed> $options Connector configuration (ignored).
     * @return ConnectorContract
     */
    public function createNullDriver(array $options): ConnectorContract
    {
        return new NullConnector();
    }
}
