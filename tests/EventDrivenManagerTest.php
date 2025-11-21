<?php

namespace LeandroSe\LaravelEventDriven\Tests;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use LeandroSe\LaravelEventDriven\Message;

class EventDrivenManagerTest extends TestCase
{
    use DatabaseMigrations;

    public function testAddListen()
    {
        $this->assertArrayNotHasKey(EventDrivenManagerTest::class, config('event-driven.listeners'));

        app('event-driven')->listen(EventDrivenManagerTest::class, ThisDomainListener::class);
        $config = config('event-driven.listeners');
        $this->assertArrayHasKey(EventDrivenManagerTest::class, $config);
        $this->assertEquals([ThisDomainListener::class], $config[EventDrivenManagerTest::class]);

        app('event-driven')->listen(EventDrivenManagerTest::class, ThisDomainListener::class);
        $config = config('event-driven.listeners');
        $this->assertArrayHasKey(EventDrivenManagerTest::class, $config);
        $this->assertEquals([ThisDomainListener::class], $config[EventDrivenManagerTest::class]);

        app('event-driven')->listen(EventDrivenManagerTest::class, Message::class);
        $config = config('event-driven.listeners');
        $this->assertArrayHasKey(EventDrivenManagerTest::class, $config);
        $this->assertEquals([ThisDomainListener::class, Message::class], $config[EventDrivenManagerTest::class]);
    }
}