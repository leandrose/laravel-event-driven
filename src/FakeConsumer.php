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
     * @return void
     */
    public function run(callable $callback)
    {
        foreach ($this->messages as $message) {
            $callback(new Message($message['topic'], $message['payload']));
        }
    }
}
