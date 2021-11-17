<?php
namespace Psy\Test;
use Psy\Autoloader;
class AutoloaderTest extends \PHPUnit_Framework_TestCase
{
    public function testRegister()
    {
        Autoloader::register();
        $this->assertTrue(spl_autoload_unregister(array('Psy\Autoloader', 'autoload')));
    }
}
