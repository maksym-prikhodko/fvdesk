<?php
namespace Monolog\Handler;
use Monolog\Logger;
use Monolog\Formatter\JsonFormatter;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Channel\AMQPChannel;
use AMQPExchange;
class AmqpHandler extends AbstractProcessingHandler
{
    protected $exchange;
    protected $exchangeName;
    public function __construct($exchange, $exchangeName = 'log', $level = Logger::DEBUG, $bubble = true)
    {
        if ($exchange instanceof AMQPExchange) {
            $exchange->setName($exchangeName);
        } elseif ($exchange instanceof AMQPChannel) {
            $this->exchangeName = $exchangeName;
        } else {
            throw new \InvalidArgumentException('PhpAmqpLib\Channel\AMQPChannel or AMQPExchange instance required');
        }
        $this->exchange = $exchange;
        parent::__construct($level, $bubble);
    }
    protected function write(array $record)
    {
        $data = $record["formatted"];
        $routingKey = sprintf(
            '%s.%s',
            substr($record['level_name'], 0, 4),
            $record['channel']
        );
        if ($this->exchange instanceof AMQPExchange) {
            $this->exchange->publish(
                $data,
                strtolower($routingKey),
                0,
                array(
                    'delivery_mode' => 2,
                    'Content-type' => 'application/json'
                )
            );
        } else {
            $this->exchange->basic_publish(
                new AMQPMessage(
                    (string) $data,
                    array(
                        'delivery_mode' => 2,
                        'content_type' => 'application/json'
                    )
                ),
                $this->exchangeName,
                strtolower($routingKey)
            );
        }
    }
    protected function getDefaultFormatter()
    {
        return new JsonFormatter(JsonFormatter::BATCH_MODE_JSON, false);
    }
}
