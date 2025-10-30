<?php

namespace LeandroSe\LaravelEventDriven;

use Illuminate\Contracts\Container\Container;
use JsonException;
use RdKafka;

/**
 * Apache Kafka implementation of {@see ConnectorContract} backed by the php-rdkafka extension.
 */
class KafkaConnector implements ConnectorContract
{

    /**
     * @param Container $container Laravel container for resolving future dependencies.
     * @param string $bootstrapServer Kafka bootstrap servers string.
     */
    public function __construct(protected Container $container, private string $bootstrapServer)
    {
    }

    /**
     * Kafka topics must be created manually (or via broker auto-creation); this method is a no-op placeholder.
     *
     * @param string $topic
     * @param array $configs
     * @return bool Always returns true, maintaining compatibility with connectors that support provisioning.
     */
    public function createTopic(string $topic, array $configs): bool
    {
        return true;
    }

    /**
     * Publish a JSON payload to Kafka using the configured bootstrap servers.
     *
     * @param string $topic
     * @param array $payload
     * @return bool True when the producer flushes successfully.
     *
     * @throws JsonException When encoding the payload fails.
     */
    public function push(string $topic, array $payload): bool
    {
        $conf = new RdKafka\Conf();
        $conf->set('metadata.broker.list', $this->bootstrapServer);

        $producer = new RdKafka\Producer($conf);
        $topic = $producer->newTopic($topic);

        $payload = json_encode($payload, JSON_THROW_ON_ERROR);

        $topic->produce(RD_KAFKA_PARTITION_UA, 0, $payload);
        $producer->flush(10000);
        return true;
    }

    /**
     * Build a Kafka consumer for the provided topics.
     *
     * @param string|string[] $topic Topics to subscribe to.
     * @param array $arguments Connector-specific arguments. Must include the `group.id`.
     * @return ConsumerContract
     *
     * @throws InvalidArgumentException When the required `group.id` is missing.
     */
    public function consumer(mixed $topic, array $arguments = []): ConsumerContract
    {
        $conf = new RdKafka\Conf();
        if (!isset($arguments['group.id'])) {
            throw new InvalidArgumentException('The group.id argument is required.');
        }
        if (is_string($topic)) {
            $topic = [$topic];
        }
        $conf->set('group.id', $arguments['group.id']);
        $conf->set('metadata.broker.list', $this->bootstrapServer);
        $conf->set('enable.partition.eof', 'true');
        $conf->set('auto.offset.reset', 'latest');

        $consumer = new RdKafka\KafkaConsumer($conf);
        return new KafkaConsumer($consumer, $topic);
    }
}
