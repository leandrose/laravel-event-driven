<?php

namespace LeandroSe\LaravelEventDriven;

/**
 * Lightweight DTO representing a message consumed or produced by a connector.
 */
class Message
{

    /**
     * @param string $topic Topic or queue name carrying the message.
     * @param array $payload Decoded payload body.
     */
    public function __construct(public string $topic, public array $payload)
    {
    }
}
