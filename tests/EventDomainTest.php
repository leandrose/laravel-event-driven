<?php

namespace LeandroSe\LaravelEventDriven\Tests;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Event;
use LeandroSe\LaravelEventDriven\Models\OutboxEvent;

class EventDomainTest extends TestCase
{
    use DatabaseMigrations;

    public function testCanCreateOutbox()
    {
        $event = new ThisDomainEvent(999);
        Event::dispatch($event);

        $this->assertEquals(1, OutboxEvent::count());
    }
}
