<?php

namespace LeandroSe\LaravelEventDriven;

use Illuminate\Support\Str;

class SupervisorOptions
{

    public string $name;
    public string $connection;
    public int $instances;
    public array $topics;
    public ?string $groupId;
    public string $memory;

    public function __construct(string  $name,
                                string  $connection,
                                int     $instances = 1,
                                array   $topics = [],
                                ?string $groupId = null,
                                string  $memory = '128M')
    {
        $this->name = $name;
        $this->connection = $connection;
        $this->instances = $instances;
        $this->topics = $topics;
        $this->groupId = $groupId;
        $this->memory = $memory;
    }

    public function toArray()
    {
        return [
            'connection' => $this->connection,
            'instances' => $this->instances,
            'topics' => $this->topics,
            'group.id' => $this->groupId,
            'memory' => $this->memory,
        ];
    }

    public static function fromArray(string $name, array $array)
    {
        return tap(new static($name, $array['connection']), function ($options) use ($array) {
            foreach ($array as $key => $value) {
                $options->{Str::camel(str_replace('.', '-', $key))} = $value;
            }
        });
    }

    /**
     * @throws EventDrivenException
     */
    public function toArgsByProcessor(bool $isInstances = true): array
    {
        $args = ['--connection=' . $this->connection];
        if ($isInstances) {
            $args[] = '--instances=' . $this->instances;
        }
        $connection = config('event-driven.connections.' . $this->connection);
        switch ($connection['driver']) {
            case 'kafka':
                foreach ($this->topics as $topic) {
                    $args[] = '--topics=' . $topic;
                }
                if (empty($this->groupId)) {
                    throw new EventDrivenException('The group_id option is required');
                }
                $args[] = '--group_id=' . $this->groupId;
                break;
        }
        return $args;
    }
}