<?php

namespace LeandroSe\LaravelEventDriven;

/**
 * Consumer that intentionally does nothing. Used alongside {@see NullConnector}.
 */
class NullConsumer implements ConsumerContract
{

    public function __construct()
    {
    }

    /**
     * No-op execution.
     *
     * @param callable $callback
     * @return void
     */
    public function run(callable $callback)
    {
    }
}
