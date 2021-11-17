<?php
namespace Symfony\Component\HttpKernel\Tests\Config;
use Symfony\Component\HttpKernel\Config\FileLocator;
class FileLocatorTest extends \PHPUnit_Framework_TestCase
{
    public function testLocate()
    {
        $kernel = $this->getMock('Symfony\Component\HttpKernel\KernelInterface');
        $kernel
            ->expects($this->atLeastOnce())
            ->method('locateResource')
            ->with('@BundleName/some/path', null, true)
            ->will($this->returnValue('/bundle-name/some/path'));
        $locator = new FileLocator($kernel);
        $this->assertEquals('/bundle-name/some/path', $locator->locate('@BundleName/some/path'));
        $kernel
            ->expects($this->never())
            ->method('locateResource');
        $this->setExpectedException('LogicException');
        $locator->locate('/some/path');
    }
    public function testLocateWithGlobalResourcePath()
    {
        $kernel = $this->getMock('Symfony\Component\HttpKernel\KernelInterface');
        $kernel
            ->expects($this->atLeastOnce())
            ->method('locateResource')
            ->with('@BundleName/some/path', '/global/resource/path', false);
        $locator = new FileLocator($kernel, '/global/resource/path');
        $locator->locate('@BundleName/some/path', null, false);
    }
}
