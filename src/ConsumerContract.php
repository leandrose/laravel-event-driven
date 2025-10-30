<?php

namespace LeandroSe\LaravelEventDriven;

/**
 * Represents a message consumer that can pull and handle messages from a connector.
 */
interface ConsumerContract
{

    /**
     * Start consuming messages and forward them to the provided callback.
     *
     * @param callable $callback Callback that receives a {@see Message} instance for each consumed event.
     * @return mixed Connector-specific return value (usually void). Implementations are allowed to run indefinitely.
     */
    public function run(callable $callback);
}
