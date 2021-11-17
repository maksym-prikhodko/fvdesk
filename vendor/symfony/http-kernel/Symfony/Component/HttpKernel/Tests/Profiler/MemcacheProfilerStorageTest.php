<?php
namespace Symfony\Component\HttpKernel\Tests\Profiler;
use Symfony\Component\HttpKernel\Profiler\MemcacheProfilerStorage;
use Symfony\Component\HttpKernel\Tests\Profiler\Mock\MemcacheMock;
class MemcacheProfilerStorageTest extends AbstractProfilerStorageTest
{
    protected static $storage;
    protected function setUp()
    {
        $memcacheMock = new MemcacheMock();
        $memcacheMock->addServer('127.0.0.1', 11211);
        self::$storage = new MemcacheProfilerStorage('memcache:
        self::$storage->setMemcache($memcacheMock);
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
