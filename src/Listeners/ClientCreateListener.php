<?php

namespace LeandroSe\LaravelEventDriven\Listeners;

use LeandroSe\LaravelEventDriven\Message;

class ClientCreateListener
{

    public function handle(Message $msg)
    {
        echo 'ClientCreateListener';
    }
}