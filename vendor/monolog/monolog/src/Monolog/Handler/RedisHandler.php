<?php
namespace Monolog\Handler;
use Monolog\Logger;
use Monolog\Formatter\LineFormatter;
class RedisHandler extends AbstractProcessingHandler
{
    private $redisClient;
    private $redisKey;
    public function __construct($redis, $key, $level = Logger::DEBUG, $bubble = true)
    {
        if (!(($redis instanceof \Predis\Client) || ($redis instanceof \Redis))) {
            throw new \InvalidArgumentException('Predis\Client or Redis instance required');
        }
        $this->redisClient = $redis;
        $this->redisKey = $key;
        parent::__construct($level, $bubble);
    }
    protected function write(array $record)
    {
        $this->redisClient->rpush($this->redisKey, $record["formatted"]);
    }
    protected function getDefaultFormatter()
    {
        return new LineFormatter();
    }
}
