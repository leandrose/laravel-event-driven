<?php

namespace LeandroSe\LaravelEventDriven;

use Closure;
use Exception;
use Symfony\Component\Process\Process;

class WorkerProcess
{

    public $process;
    public $output;

    public function __construct(Process $process)
    {
        $this->process = $process;
    }

    public function start(Closure $callback)
    {
        $this->output = $callback;
    }

    public function stop()
    {
        if ($this->process->isRunning()) {
            $this->process->stop();
        }
    }

    public function terminate()
    {
        $this->sendSignal(SIGTERM);
    }

    public function monitor()
    {
        if ($this->process->isRunning()) {
            return;
        }

        $this->restart();
    }

    public function restart()
    {
        $this->start($this->output);
    }

    public function sendSignal(int $signal)
    {
        try {
            $this->process->signal($signal);
        } catch (Exception $e) {
            if ($this->process->isRunning()) {
                throw $e;
            }
        }
    }
}