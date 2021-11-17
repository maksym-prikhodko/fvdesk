<?php
namespace Symfony\Component\HttpKernel\Tests\Profiler;
use Symfony\Component\HttpKernel\Profiler\RedisProfilerStorage;
use Symfony\Component\HttpKernel\Tests\Profiler\Mock\RedisMock;
class RedisProfilerStorageTest extends AbstractProfilerStorageTest
{
    protected static $storage;
    protected function setUp()
    {
        $redisMock = new RedisMock();
        $redisMock->connect('127.0.0.1', 6379);
        self::$storage = new RedisProfilerStorage('redis:
        self::$storage->setRedis($redisMock);
        if (self::$storage) {
            self::$storage->purge();
        }
    }
    protected function tearDown()
    {
        if (self::$storage) {
            self::$storage->purge();
            self::$storage = false;
        }
    }
    protected function getStorage()
    {
        return self::$storage;
    }
}
