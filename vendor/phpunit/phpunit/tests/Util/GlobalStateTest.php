<?php
class Util_GlobalStateTest extends PHPUnit_Framework_TestCase
{
    public function testIncludedFilesAsStringSkipsVfsProtocols()
    {
        $dir = __DIR__;
        $files = array(
            'phpunit', 
            $dir . '/ConfigurationTest.php',
            $dir . '/GlobalStateTest.php',
            'vfs:
            'phpvfs53e46260465c7:
            'file:
        );
        $this->assertEquals(
            "require_once '" . $dir . "/ConfigurationTest.php';\n" .
            "require_once '" . $dir . "/GlobalStateTest.php';\n" .
            "require_once 'file:
    }
}
