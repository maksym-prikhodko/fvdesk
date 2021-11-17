<?php
namespace Symfony\Component\HttpKernel\Tests\Bundle;
use Symfony\Component\HttpKernel\Tests\Fixtures\ExtensionPresentBundle\ExtensionPresentBundle;
use Symfony\Component\HttpKernel\Tests\Fixtures\ExtensionAbsentBundle\ExtensionAbsentBundle;
use Symfony\Component\HttpKernel\Tests\Fixtures\ExtensionPresentBundle\Command\FooCommand;
class BundleTest extends \PHPUnit_Framework_TestCase
{
    public function testRegisterCommands()
    {
        $cmd = new FooCommand();
        $app = $this->getMock('Symfony\Component\Console\Application');
        $app->expects($this->once())->method('add')->with($this->equalTo($cmd));
        $bundle = new ExtensionPresentBundle();
        $bundle->registerCommands($app);
        $bundle2 = new ExtensionAbsentBundle();
        $this->assertNull($bundle2->registerCommands($app));
    }
}
