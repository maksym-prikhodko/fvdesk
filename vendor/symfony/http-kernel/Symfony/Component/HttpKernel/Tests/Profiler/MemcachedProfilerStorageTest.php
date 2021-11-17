<?php
namespace Symfony\Component\HttpKernel\Tests\Profiler;
use Symfony\Component\HttpKernel\Profiler\MemcachedProfilerStorage;
use Symfony\Component\HttpKernel\Tests\Profiler\Mock\MemcachedMock;
class MemcachedProfilerStorageTest extends AbstractProfilerStorageTest
{
    protected static $storage;
    protected function setUp()
    {
        $memcachedMock = new MemcachedMock();
        $memcachedMock->addServer('127.0.0.1', 11211);
        self::$storage = new MemcachedProfilerStorage('memcached:
        self::$storage->setMemcached($memcachedMock);
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
