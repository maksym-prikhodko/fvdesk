<?php
namespace Symfony\Component\HttpKernel\Tests\Profiler;
use Symfony\Component\HttpKernel\Profiler\SqliteProfilerStorage;
class SqliteProfilerStorageTest extends AbstractProfilerStorageTest
{
    protected static $dbFile;
    protected static $storage;
    public static function setUpBeforeClass()
    {
        self::$dbFile = tempnam(sys_get_temp_dir(), 'sf2_sqlite_storage');
        if (file_exists(self::$dbFile)) {
            @unlink(self::$dbFile);
        }
        self::$storage = new SqliteProfilerStorage('sqlite:'.self::$dbFile);
    }
    public static function tearDownAfterClass()
    {
        @unlink(self::$dbFile);
    }
    protected function setUp()
    {
        if (!class_exists('SQLite3') && (!class_exists('PDO') || !in_array('sqlite', \PDO::getAvailableDrivers()))) {
            $this->markTestSkipped('This test requires SQLite support in your environment');
        }
        self::$storage->purge();
    }
    protected function getStorage()
    {
        return self::$storage;
    }
}
