<?php

namespace LeandroSe\LaravelEventDriven;

use Closure;
use Symfony\Component\Process\Process;

abstract class AbstractSupervisor
{

    /**
     * @var array|Process[]
     */
    protected array $processes = [];
    protected bool $isRunning = false;
    protected ?Closure $output = null;

    public function monitor(): void
    {
        $this->isRunning = true;
        while ($this->isRunning) {
            sleep(1);

            $this->loop();
        }
    }

    public function loop(): void
    {
        /**
         * @var int $i
         * @var  Process $process
         */
        foreach ($this->processes as $i => $process) {
            if (!$process->isStarted()) {
                $process->start(function ($type, $buffer) {
                    $output = $this->output;
                    $output($buffer);
                });
                continue;
            }
            if (!$process->isRunning()) {
                $cmd = $process->getCommandLine();
                $new = Process::fromShellCommandline($cmd);
                $new->setTimeout(null);
                $new->start(function ($type, $buffer) {
                    $output = $this->output;
                    $output($buffer);
                });
                $this->processes[$i] = $new;
                continue;
            }
        }
    }

    public function stop(): void
    {
        foreach ($this->processes as $process) {
            if ($process->isRunning()) {
                $process->signal(SIGTERM);
            }
        }
        $this->isRunning = false;
    }

    public function handleOutput(?Closure $callback): void
    {
        $this->output = $callback;
    }
}