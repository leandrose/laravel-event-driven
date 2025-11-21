<?php

return [
    'default' => env('EVENT_DRIVEN_CONNECTION', 'kafka'),

    'outbox_event' => true,

    'connections' => [
        'kafka' => [
            'driver' => 'kafka',
            'bootstrap_servers' => env('EVENT_DRIVEN_KAFKA_BOOTSTRAP_SERVERS', 'localhost:9092'),
        ],
        'null' => [
            'driver' => 'null',
        ],
        'fake' => [
            'driver' => 'fake',
        ],
    ],

    'supervisors' => [
//        'clients_kafka' => [
//            'connection' => 'kafka',
//            'topics' => ['client.create', 'client.update', 'client.delete'],
//            'backoff' => 30,
//
//            'instances' => 3,
//            'group.id' => 'clientes',
//        ],
    ],

    'listeners' => [
    ],
];
