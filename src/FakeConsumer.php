<?php

namespace LeandroSe\LaravelEventDriven;

/**
 * Consumer that replays a predefined list of messages stored by {@see FakeConnector}.
 */
class FakeConsumer implements ConsumerContract
{

    /**
     * @param array<int, array{topic:string,payload:array}> $messages
     */
    public function __construct(protected array &$messages)
    {
    }

    /**
     * Invoke the callback for each queued message.
     *
     * @param callable $callback
     * @param bool &$isRunning
     * @return void
     */
    public function run(callable $callback, bool &$isRunning)
    {
        foreach ($this->messages as $message) {
            if (!$isRunning) {
                return;
            }
            $payload = $message['payload'];
            $callback(new Message($message['topic'], $payload['event_id'], $payload['occurred_at'], $payload['version'], $message['payload']));
        }
    }
}
