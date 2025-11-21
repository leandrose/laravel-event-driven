<?php

namespace LeandroSe\LaravelEventDriven\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use LeandroSe\LaravelEventDriven\ConnectorContract;
use LeandroSe\LaravelEventDriven\ConnectorNotFoundException;
use LeandroSe\LaravelEventDriven\Console\Commands\Traits\PrintTrait;
use LeandroSe\LaravelEventDriven\EventDrivenException;
use LeandroSe\LaravelEventDriven\EventDrivenManager;
use LeandroSe\LaravelEventDriven\FakeConnector;
use LeandroSe\LaravelEventDriven\InvalidArgumentException;
use LeandroSe\LaravelEventDriven\KafkaConnector;
use LeandroSe\LaravelEventDriven\Message;
use LeandroSe\LaravelEventDriven\NullConnector;
use ReflectionClass;
use ReflectionNamedType;

class WorkerCommand extends Command
{
    use PrintTrait;

    protected $signature = 'event-driven:worker
                           {name : The name of the worker}
                           {--connection= : The name of the connection to use}
                           {--topics=* : The topics to consume}
                           {--group_id= : The consumer group id}
                           {--memory=128M : The memory limit for the worker process}';
    protected $description = 'Start a worker process';

    protected bool $running = true;

    /**
     * @throws EventDrivenException
     * @throws ConnectorNotFoundException
     * @throws InvalidArgumentException
     */
    public function handle(EventDrivenManager $eventDriven)
    {
        ini_set('memory_limit', $this->option('memory'));
        $this->line(sprintf('Worker "%s" started', $this->argument('name')));

        pcntl_async_signals(true);
        pcntl_signal(SIGINT, function () {
            $this->info(sprintf('Worker "%s" received a signal to finish.', $this->argument('name')));

            $this->running = false;
        });

        $connection = $eventDriven->connection($this->option('connection') ?? config('event-driven.default'));
        $consumer = $this->instanceByDriver($connection);
        $listeners = config('event-driven.listeners');
        $consumer->run(function (Message $msg) use ($listeners) {
            $this->printRunning($msg);
            try {
                if (isset($listeners[$msg->topic])) {
                    foreach ($listeners[$msg->topic] as $listener) {
                        if ($this->isValidHandler($listener)) {
                            $listen = app($listener);
                            $listen->handle($msg);
                        }
                    }
                }
                $this->printSuccess($msg);
            } catch (Exception) {
                $this->printError($msg);
            }
        }, $this->running);

        $this->info(sprintf('Worker "%s" has finished', $this->argument('name')));
    }

    protected function isValidHandler(string $class): bool
    {
        if (!class_exists($class)) {
            return false;
        }
        $ref = new ReflectionClass($class);
        if (!$ref->hasMethod('handle')) {
            return false;
        }
        $method = $ref->getMethod('handle');
        $params = $method->getParameters();
        if (count($params) == 0) {
            return false;
        }
        $type = $params[0]->getType();
        if (!$type instanceof ReflectionNamedType) {
            return false;
        }
        return $type->getName() === Message::class;
    }

    /**
     * Build a new consumer by driver.
     *
     * @throws EventDrivenException
     * @throws InvalidArgumentException
     */
    public
    function instanceByDriver(ConnectorContract $connector)
    {
        $topics = $this->option('topics');
        if (empty($topics)) {
            throw new EventDrivenException('The topics option is required');
        }
        if (is_string($topics)) {
            $topics = [$topics];
        }

        switch (get_class($connector)) {
            case NullConnector::class:
            case FakeConnector::class:
                return $connector->consumer($topics);
            case KafkaConnector::class:
                $group_id = $this->option('group_id');
                if (empty($group_id)) {
                    throw new EventDrivenException('The group_id option is required');
                }
                return $connector->consumer($topics, [
                    'group.id' => $group_id,
                ]);
            default:
                throw new EventDrivenException('The driver ' . get_class($connector) . ' is not supported');
        }
    }
}