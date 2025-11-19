<?php

namespace LeandroSe\LaravelEventDriven\Console\Commands;

use Illuminate\Console\Command;
use LeandroSe\LaravelEventDriven\EventDrivenException;
use LeandroSe\LaravelEventDriven\Supervisor;
use LeandroSe\LaravelEventDriven\SupervisorOptions;

class SupervisorCommand extends Command
{

    protected $signature = 'event-driven:supervisor
                           {name : The name of the supervisor}
                           {--connection= : The name of the connection to use}
                           {--instances=1 : The number of instances to run}
                           {--topics=* : The topics to subscribe to}
                           {--group_id= : The consumer group id}
                           {--memory=128M : The memory limit for the worker process}';
    protected $description = 'Start a new supervisor';


    /**
     * @throws EventDrivenException
     */
    public function handle()
    {
        ini_set('memory_limit', $this->option('memory'));
        $this->line(sprintf('Supervisor "%s" started', $this->argument('name')));

        $supervisor = new Supervisor($this->supervisorOptions(), function ($file) {
            $this->line(trim($file));
        });

        pcntl_async_signals(true);
        pcntl_signal(SIGINT, function () use ($supervisor) {
            $this->info(sprintf('Supervisor "%s" received a signal to finish', $this->argument('name')));

            $supervisor->stop();
        });

        $supervisor->monitor();

        sleep(2);
        $this->line(sprintf('Supervisor "%s" has finished', $this->argument('name')));
    }

    public function supervisorOptions()
    {
        return new SupervisorOptions(
            name: $this->argument('name'),
            connection: $this->option('connection'),
            instances: $this->option('instances') ?? 1,
            topics: $this->option('topics') ?? [],
            groupId: $this->option('group_id'),
            memory: $this->option('memory'),
        );
    }
}