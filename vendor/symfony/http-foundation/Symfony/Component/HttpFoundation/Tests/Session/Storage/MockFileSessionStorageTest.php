<?php
namespace Symfony\Component\HttpFoundation\Tests\Session\Storage;
use Symfony\Component\HttpFoundation\Session\Storage\MockFileSessionStorage;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBag;
class MockFileSessionStorageTest extends \PHPUnit_Framework_TestCase
{
    private $sessionDir;
    protected $storage;
    protected function setUp()
    {
        $this->sessionDir = sys_get_temp_dir().'/sf2test';
        $this->storage = $this->getStorage();
    }
    protected function tearDown()
    {
        $this->sessionDir = null;
        $this->storage = null;
        array_map('unlink', glob($this->sessionDir.'
    public function testSaveWithoutStart()
    {
        $storage1 = $this->getStorage();
        $storage1->save();
    }
    private function getStorage()
    {
        $storage = new MockFileSessionStorage($this->sessionDir);
        $storage->registerBag(new FlashBag());
        $storage->registerBag(new AttributeBag());
        return $storage;
    }
}
