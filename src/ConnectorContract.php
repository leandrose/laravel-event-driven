<?php

namespace LeandroSe\LaravelEventDriven;

/**
 * Describes the capabilities required by every messaging connector implementation.
 * A connector must be able to provision topics (when supported), publish messages, and create consumers.
 */
interface ConnectorContract
{

    /**
     * Provision a topic/queue on the underlying broker, when the driver supports it.
     *
     * @param string $topic Name of the topic or queue to be created.
     * @param array $configs Connector-specific configuration for the topic.
     * @return bool Whether the broker accepted the request. Some drivers may be no-ops that still return true for interface parity.
     */
    public function createTopic(string $topic, array $configs): bool;

    /**
     * Publish a payload to the given topic or queue.
     *
     * @param string $topic Destination topic or queue.
     * @param array $payload Serializable payload to be sent.
     * @return bool True when the message was queued for delivery.
     */
    public function push(string $topic, array $payload): bool;

    /**
     * Build a consumer configured to receive messages from the provided topics/queues.
     *
     * @param string|string[] $topic Single topic name or a list of topics.
     * @param array $arguments Optional, driver-specific consumer configuration.
     * @return ConsumerContract Consumer ready to be executed.
     */
    public function consumer(mixed $topic, array $arguments = []): ConsumerContract;
}
