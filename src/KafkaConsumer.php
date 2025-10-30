<?php

namespace LeandroSe\LaravelEventDriven;

use JsonException;
use RdKafka;

/**
 * Consumer wrapper around {@see RdKafka\KafkaConsumer} that adapts messages to the package format.
 */
class KafkaConsumer implements ConsumerContract
{

    /**
     * @param RdKafka\KafkaConsumer $consumer Low-level Kafka consumer.
     * @param array $topics Topics to subscribe to.
     */
    public function __construct(protected RdKafka\KafkaConsumer $consumer,
                                protected array                 $topics)
    {
    }

    /**
     * Subscribe to the configured topics and continuously consume messages.
     *
     * @param callable $callback Invoked for each received {@see Message}.
     * @return void
     *
     * @throws JsonException When decoding the payload fails.
     */
    public function run(callable $callback)
    {
        $this->consumer->subscribe($this->topics);

        while (true) {
            $message = $this->consumer->consume(30 * 1000);
            switch ($message->err) {
                case RD_KAFKA_RESP_ERR_NO_ERROR:
                    $msg = new Message($message->topic_name, json_decode($message->payload, true, JSON_THROW_ON_ERROR));
                    $callback($msg);
                    break;
                case RD_KAFKA_RESP_ERR__PARTITION_EOF:
                case RD_KAFKA_RESP_ERR__TIMED_OUT:
                    usleep(100000);
                    break;
            }
        }
    }
}
