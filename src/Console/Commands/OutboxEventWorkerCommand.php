<?php

namespace LeandroSe\LaravelEventDriven\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use JsonException;
use LeandroSe\LaravelEventDriven\ConnectorContract;
use LeandroSe\LaravelEventDriven\Models\OutboxEvent;

/**
 * Artisan worker that drains the outbox table and forwards events to the configured connector.
 */
class OutboxEventWorkerCommand extends Command
{

    protected $signature = 'event-driven:outbox
                            {--delay=300000 : Delay in microseconds}';

    protected $description = 'Worker that publishes persisted outbox events to the configured connector.';

    /**
     * Run the worker loop until interrupted.
     *
     * @param ConnectorContract $eventDriven
     * @return void
     *
     * @throws JsonException When stored payloads cannot be decoded.
     */
    public function handle(ConnectorContract $eventDriven): void
    {
        $query = OutboxEvent::queryUnprocessed();
        while (true) {
            /** @var Collection<int, OutboxEvent> $events */
            $events = $query->get();
            if ($events->count() > 0) {
                foreach ($events as $item) {
                    $eventDriven->push($item->event_name, json_decode($item->payload, JSON_THROW_ON_ERROR));
                    $item->processed_at = Carbon::now();
                    $item->save();
                    $this->print($item->event_name, $item->getKey());
                }
            } else {
                usleep(intval($this->option('delay')));
            }
        }
    }

    /**
     * Output a single progress line indicating a processed event.
     *
     * @param string $eventName
     * @param int $id
     * @return void
     */
    public function print(string $eventName, int $id): void
    {
        $beginning = sprintf('  %s %s(%d) ', now()->format('Y-m-d H:i:s'), $eventName, $id);
        $end = ' DONE';
        $separator = '';
        $count = 80 - strlen($beginning . $end);
        if ($count > 0) {
            $separator = str_repeat('.', $count);
        }
        $this->line($beginning . $separator . $end);
    }
}
