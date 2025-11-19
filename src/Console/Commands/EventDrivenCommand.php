<?php

namespace LeandroSe\LaravelEventDriven\Console\Commands;

use Illuminate\Console\Command;
use LeandroSe\LaravelEventDriven\EventDrivenException;
use LeandroSe\LaravelEventDriven\MasterSupervisor;

class EventDrivenCommand extends Command
{

    protected $signature = 'event-driven';
    protected $description = 'Start a master supervisor';

    protected array $supervisors = [];

    /**
     * @throws EventDrivenException
     */
    public function handle()
    {
        $this->info('Event-Driven started successfully.');

        $master = new MasterSupervisor(function ($line) {
            $this->line(trim($line));
        });

        pcntl_async_signals(true);
        pcntl_signal(SIGINT, function () use (&$master) {
            $master->stop();

            $this->info('Event-Driven received a signal to finish');
        });

        $master->monitor();

        sleep(3);
        $this->info('Event-Driven has finished');
    }
}