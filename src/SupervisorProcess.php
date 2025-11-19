<?php

namespace LeandroSe\LaravelEventDriven;

use Closure;

class SupervisorProcess extends WorkerProcess
{

    public string $name;
    public SupervisorOptions $options;
    public $dead = false;
    public Closure $output;

    public function __construct(SupervisorOptions $options, Closure $output)
    {
        $this->name = $options->name;
        $this->options = $options;
        $this->output = $output;
    }
}