<?php
namespace Monolog;
class RegistryTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        Registry::clear();
    }
    public function testHasLogger(array $loggersToAdd, array $loggersToCheck, array $expectedResult)
    {
        foreach ($loggersToAdd as $loggerToAdd) {
            Registry::addLogger($loggerToAdd);
        }
        foreach ($loggersToCheck as $index => $loggerToCheck) {
            $this->assertSame($expectedResult[$index], Registry::hasLogger($loggerToCheck));
        }
    }
    public function hasLoggerProvider()
    {
        $logger1 = new Logger('test1');
        $logger2 = new Logger('test2');
        $logger3 = new Logger('test3');
        return array(
            array(
                array($logger1),
                array($logger1, $logger2),
                array(true, false),
            ),
            array(
                array($logger1),
                array('test1', 'test2'),
                array(true, false),
            ),
            array(
                array($logger1, $logger2),
                array('test1', $logger2, 'test3', $logger3),
                array(true, true, false, false),
            ),
        );
    }
}
