<?php

namespace LeandroSe\LaravelEventDriven\Tests;

use LeandroSe\LaravelEventDriven\DomainEvent;

class ThisDomainEvent extends DomainEvent
{

    public function __construct(protected int $idClient)
    {
        parent::__construct();
    }

    public static function name(): string
    {
        return 'client';
    }

    public function payload(): array
    {
        return ['client_id' => $this->idClient];
    }
}