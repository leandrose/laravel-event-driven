<?php

namespace LeandroSe\LaravelEventDriven;

use Closure;
use Symfony\Component\Process\Process;

class MasterSupervisor extends AbstractSupervisor
{

    /**
     * @throws EventDrivenException
     */
    public function __construct(Closure $output)
    {
        $this->handleOutput($output);

        $supervisors = config('event-driven.supervisors');
        foreach ($supervisors as $name => $supervisor) {
            $args = [
                'php',
                'artisan',
                'event-driven:supervisor',
                $name,
            ];
            $args = array_merge($args, SupervisorOptions::fromArray($name, $supervisor)->toArgsByProcessor());

            $process = new Process($args);
            $process->setTimeout(null);
            // $process->start([$this, 'output']);
            $this->processes[] = $process;
        }
    }
}