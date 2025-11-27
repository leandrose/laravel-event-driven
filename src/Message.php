<?php

namespace LeandroSe\LaravelEventDriven;

use DateTimeInterface;

/**
 * Lightweight DTO representing a message consumed or produced by a connector.
 */
class Message
{

    /**
     * @param string $topic Topic or queue name carrying the message.
     * @param string $eventId Event identifier.
     * @param DateTimeInterface $occurredAt Event occurrence timestamp.
     * @param int $version Message version.
     * @param array $payload Decoded payload body.
     */
    public function __construct(public string            $topic,
                                public string            $eventId,
                                public DateTimeInterface $occurredAt,
                                public int               $version,
                                public array             $payload)
    {
    }
}
