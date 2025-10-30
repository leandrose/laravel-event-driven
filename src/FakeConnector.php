<?php

namespace LeandroSe\LaravelEventDriven;

/**
 * In-memory connector tailored for tests where syncing messages within the same process is desirable.
 */
class FakeConnector implements ConnectorContract
{

    /**
     * @var array<int, array{topic:string,payload:array}>
     */
    protected array $messages = [];

    /**
     * Pretend to create a topic and keep the API consistent with other connectors.
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
     * Return a consumer that replays the messages accumulated so far.
     *
     * @param mixed $topic
     * @param array $arguments
     * @return ConsumerContract
     */
    public function consumer(mixed $topic, array $arguments = []): ConsumerContract
    {
        return new FakeConsumer($this->messages);
    }

    /**
     * Store the payload in memory for later inspection.
     *
     * @param string $topic
     * @param array $payload
     * @return bool
     */
    public function push(string $topic, array $payload): bool
    {
        $this->messages[] = compact('topic', 'payload');
        return true;
    }

    /**
     * Number of messages currently stored in memory.
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->messages);
    }
}
