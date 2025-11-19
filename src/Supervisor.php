<?php

namespace LeandroSe\LaravelEventDriven;

use Closure;
use Symfony\Component\Process\Process;

class Supervisor extends AbstractSupervisor
{

    protected SupervisorOptions $options;

    /**
     * @throws EventDrivenException
     */
    public function __construct(SupervisorOptions $options, Closure $output)
    {
        $this->handleOutput($output);

        $this->options = $options;

        $oldInstance = $this->options->instances;
        for ($i = 1; $i <= $oldInstance; $i++) {
            $args = [
                'php',
                'artisan',
                'event-driven:worker',
                sprintf('%s_%d', $this->options->name, $i),
            ];
            $this->options->instances = $i;
            $args = array_merge($args, $this->options->toArgsByProcessor(isInstances: false));
            $process = new Process($args);
            $process->setTimeout(null);
            $this->processes[] = $process;
        }
        $this->options->instances = $oldInstance;
    }
}