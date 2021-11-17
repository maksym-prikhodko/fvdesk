<?php
namespace Symfony\Component\Filesystem\Tests;
use Symfony\Component\Filesystem\LockHandler;
class LockHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructWhenRepositoryDoesNotExist()
    {
        new LockHandler('lock', '/a/b/c/d/e');
    }
    public function testConstructWhenRepositoryIsNotWriteable()
    {
        new LockHandler('lock', '/');
    }
    public function testConstructSanitizeName()
    {
        $lock = new LockHandler('<?php echo "% hello word ! %" ?>');
        $file = sprintf('%s/sf.-php-echo-hello-word-.4b3d9d0d27ddef3a78a64685dda3a963e478659a9e5240feaf7b4173a8f28d5f.lock', sys_get_temp_dir());
        @unlink($file);
        $lock->lock();
        $this->assertFileExists($file);
        $lock->release();
    }
    public function testLockRelease()
    {
        $name = 'symfony-test-filesystem.lock';
        $l1 = new LockHandler($name);
        $l2 = new LockHandler($name);
        $this->assertTrue($l1->lock());
        $this->assertFalse($l2->lock());
        $l1->release();
        $this->assertTrue($l2->lock());
        $l2->release();
    }
    public function testLockTwice()
    {
        $name = 'symfony-test-filesystem.lock';
        $lockHandler = new LockHandler($name);
        $this->assertTrue($lockHandler->lock());
        $this->assertTrue($lockHandler->lock());
        $lockHandler->release();
    }
    public function testLockIsReleased()
    {
        $name = 'symfony-test-filesystem.lock';
        $l1 = new LockHandler($name);
        $l2 = new LockHandler($name);
        $this->assertTrue($l1->lock());
        $this->assertFalse($l2->lock());
        $l1 = null;
        $this->assertTrue($l2->lock());
        $l2->release();
    }
}
