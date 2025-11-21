<?php

namespace LeandroSe\LaravelEventDriven;

use DateTimeImmutable;

/**
 * Base class for domain events that should be persisted or propagated through a messaging connector.
 *
 * Each domain event carries a unique identifier and the timestamp when it occurred.
 */
abstract class DomainEvent
{

    /**
     * Unique identifier for the event instance.
     */
    public string $eventId;

    /**
     * Timestamp of when the event was instantiated.
     */
    public DateTimeImmutable $occurredAt;

    public int $version = 1;

    /**
     * Initialise the event with a unique identifier and occurrence timestamp.
     */
    public function __construct()
    {
        $this->eventId = uniqid('evt_', true);
        $this->occurredAt = new DateTimeImmutable();
    }

    /**
     * Human-readable, stable name that identifies the event type.
     *
     * @return string
     */
    abstract public static function name(): string;

    /**
     * Data payload that should be published alongside the event.
     *
     * @return array
     */
    abstract public function payload(): array;
}
