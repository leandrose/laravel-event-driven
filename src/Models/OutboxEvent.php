<?php

namespace LeandroSe\LaravelEventDriven\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use JsonException;
use LeandroSe\LaravelEventDriven\DomainEvent;

/**
 * Eloquent model that stores domain events waiting to be dispatched to external systems.
 *
 * @property string $event_name
 * @property string $payload
 * @property Carbon $occurred_at
 * @property ?Carbon $processed_at
 * @property ?Carbon $failed_at
 * @property ?string $exception
 */
class OutboxEvent extends Model
{

    protected $table = 'outbox_events';
    public $timestamps = false;
    protected $casts = [
        'occurred_at' => 'datetime',
        'processed_at' => 'datetime',
        'failed_at' => 'datetime',
    ];

    /**
     * Persist a {@see DomainEvent} into the outbox table.
     *
     * @param DomainEvent $event
     * @return OutboxEvent The newly created record.
     *
     * @throws JsonException When the payload cannot be serialised.
     */
    public static function insertByDomainEvent(DomainEvent $event): OutboxEvent
    {
        $new = new OutboxEvent();
        $new->event_name = $event->name();
        $new->payload = json_encode($event->payload(), JSON_THROW_ON_ERROR);
        $new->occurred_at = $event->occurredAt;
        $new->save();

        return $new;
    }

    /**
     * Fetch a query builder targeting unprocessed events ordered by primary key.
     *
     * @param int $limit Maximum number of records to retrieve.
     * @return Builder
     */
    public static function queryUnprocessed(int $limit = 100): Builder
    {
        return OutboxEvent::whereNull('processed_at')
            ->orderBy('id')
            ->limit($limit);
    }
}
