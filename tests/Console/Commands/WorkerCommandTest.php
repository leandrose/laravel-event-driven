<?php

namespace LeandroSe\LaravelEventDriven\Tests\Console\Commands;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Event;
use LeandroSe\LaravelEventDriven\FakeConnector;
use LeandroSe\LaravelEventDriven\Models\OutboxEvent;
use LeandroSe\LaravelEventDriven\Tests\TestCase;
use LeandroSe\LaravelEventDriven\Tests\ThisDomainEvent;
use RuntimeException;

class WorkerCommandTest extends TestCase
{
    use DatabaseMigrations;

    public function test_can_send()
    {
        for ($i = 0; $i < 50; $i++) {
            $event = new ThisDomainEvent($i);
            Event::dispatch($event);
        }

        $this->assertEquals(50, OutboxEvent::count());

        pcntl_async_signals(true);
        pcntl_signal(SIGALRM, function () {
            $count = OutboxEvent::queryUnprocessed()->count();
            $this->assertEquals(0, $count);
            /** @var FakeConnector $eventDriven */
            $eventDriven = resolve('event-driven.driver');
            $this->assertEquals(50, $eventDriven->count());
            throw new RuntimeException('Timeout reached');
        });
        pcntl_alarm(3);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Timeout reached');

        $this->artisan('event-driven:outbox')->run();

        pcntl_alarm(0);
    }
}