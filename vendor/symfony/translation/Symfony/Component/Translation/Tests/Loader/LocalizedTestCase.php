<?php
namespace Symfony\Component\Translation\Tests\Loader;
abstract class LocalizedTestCase extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        if (!extension_loaded('intl')) {
            $this->markTestSkipped('The "intl" extension is not available');
        }
    }
}
