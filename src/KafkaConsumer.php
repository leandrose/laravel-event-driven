<?php

namespace LeandroSe\LaravelEventDriven;

use DateTime;
use DateTimeInterface;
use JsonException;
use RdKafka;
use RdKafka\Exception;

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
     * @param bool $isRunning Flag indicating whether the consumer is currently running.
     * @return void
     *
     * @throws JsonException When decoding the payload fails.
     * @throws Exception
     */
    public function run(callable $callback, bool &$isRunning)
    {
        $this->consumer->subscribe($this->topics);

        while ($isRunning) {
            $message = $this->consumer->consume(500);
            switch ($message->err) {
                case RD_KAFKA_RESP_ERR_NO_ERROR:
                    $payload = json_decode($message->payload, true, JSON_THROW_ON_ERROR);
                    $msg = new Message(
                        $message->topic_name,
                        $payload['event_id'],
                        DateTime::createFromFormat(DateTimeInterface::RFC3339, $payload['occurred_at']),
                        $payload['version'],
                        $payload['payload'],
                    );
                    $callback($msg);
                    break;
                case RD_KAFKA_RESP_ERR__PARTITION_EOF:
                case RD_KAFKA_RESP_ERR__TIMED_OUT:
                    usleep(100000);
                    break;
            }
            $this->consumer->commitAsync();
        }
    }
}
