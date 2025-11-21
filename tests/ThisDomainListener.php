<?php

namespace LeandroSe\LaravelEventDriven\Tests;

use LeandroSe\LaravelEventDriven\Message;

class ThisDomainListener
{

    public function handle(Message $message)
    {
        echo 'ThisDomainListener';
    }
}