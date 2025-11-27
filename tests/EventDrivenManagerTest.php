<?php

namespace LeandroSe\LaravelEventDriven\Tests;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use LeandroSe\LaravelEventDriven\DomainEvent;
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

        app('event-driven')->listen(['a', 'b'], [Message::class, DomainEvent::class]);
        $config = config('event-driven.listeners');
        $this->assertArrayHasKey('a', $config);
        $this->assertArrayHasKey('b', $config);
        $this->assertContains(Message::class, $config['a']);
        $this->assertContains(Message::class, $config['b']);
        $this->assertContains(DomainEvent::class, $config['a']);
        $this->assertContains(DomainEvent::class, $config['b']);
    }
}