<?php

namespace LeandroSe\LaravelEventDriven;

/**
 * Connector that intentionally ignores every request. Useful for disabling messaging in specific environments.
 */
class NullConnector implements ConnectorContract
{

    /**
     * No-op topic creation.
     *
     * @param string $topic
     * @param array $configs
     * @return bool Always true.
     */
    public function createTopic(string $topic, array $configs): bool
    {
        return true;
    }

    /**
     * Return a consumer that performs no work.
     *
     * @param mixed $topic
     * @param array $arguments
     * @return ConsumerContract
     */
    public function consumer(mixed $topic, array $arguments = []): ConsumerContract
    {
        return new NullConsumer();
    }

    /**
     * Silently drop the payload.
     *
     * @param string $topic
     * @param array $payload
     * @return bool Always true.
     */
    public function push(string $topic, array $payload): bool
    {
        return true;
    }

    /**
     * Get the name of the connector.
     * @return string
     **/
    public function driverName(): string
    {
        return 'null';
    }
}
