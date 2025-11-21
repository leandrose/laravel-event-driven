<?php

namespace LeandroSe\LaravelEventDriven;

use DateTimeInterface;
use Illuminate\Events\Dispatcher as LaravelDispatcher;
use LeandroSe\LaravelEventDriven\Models\OutboxEvent;

/**
 * Decorates Laravel's event dispatcher to transparently persist or publish domain events.
 *
 * When a dispatched event implements {@see DomainEvent}, the dispatcher either records it in the outbox
 * table (when enabled) or forwards it directly to the configured messaging connector.
 */
class Dispatcher extends LaravelDispatcher
{

    /**
     * Dispatch the given event and handle DomainEvent side effects before delegating to Laravel.
     *
     * @param mixed $event Event instance or class name.
     * @param array $payload Optional payload, as defined by the base dispatcher contract.
     * @param bool $halt Whether the dispatch should stop after the first listener returns a value.
     * @return array|null Array of listener responses, or null when halting.
     */
    public function dispatch($event, $payload = [], $halt = false): ?array
    {
        if ($event instanceof DomainEvent) {
            if ($this->container['config']['event-driven.outbox_event']) {
                OutboxEvent::insertByDomainEvent($event);
            } else {
                resolve('event-driven.driver')->push($event->name(), array_merge(
                    ['event_id' => $event->eventId, 'occurred_at' => $event->occurredAt->format(DateTimeInterface::RFC3339), 'version' => $event->version],
                    $event->payload()
                ));
            }
        }
        return parent::dispatch($event, $payload, $halt);
    }
}
