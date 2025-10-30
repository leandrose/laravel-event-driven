# Event Driven Connector

A lightweight, extensible **Event-Driven Architecture** package for PHP applications.
It provides unified interfaces for publishing, consuming, and managing events using **Apache Kafka**.

## Topic/Queue Creation

| Connector | Create Topic | Consumer  | Push Message |
|-----------|--------------|-----------|--------------|
| Kafka     | Manual       | Supported | Supported    |

## Features

- **Unified interfaces** for producers and consumers (`ConnectorInterface`, `ConsumerInterface`)
- **Kafka support out of the box** - powered by `php-rdkafka`
- **Outbox pattern ready** for reliable event delivery
- **Extensible architecture** - easily add connectors for RabbitMQ, SQS, etc.
- **Event dispatching abstraction** for seamless integration with Laravel or custom frameworks
- **Decoupled design** - reusable across microservices and monoliths

## Installation

```bash
composer require leandrose/laravel-event-driven
```
