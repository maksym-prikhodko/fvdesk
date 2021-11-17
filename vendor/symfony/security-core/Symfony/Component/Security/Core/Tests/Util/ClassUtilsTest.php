<?php
namespace Symfony\Component\Security\Core\Tests\Util
{
    use Symfony\Component\Security\Core\Util\ClassUtils;
    class ClassUtilsTest extends \PHPUnit_Framework_TestCase
    {
        public static function dataGetClass()
        {
            return array(
                array('stdClass', 'stdClass'),
                array('Symfony\Component\Security\Core\Util\ClassUtils', 'Symfony\Component\Security\Core\Util\ClassUtils'),
                array('MyProject\Proxies\__CG__\stdClass', 'stdClass'),
                array('MyProject\Proxies\__CG__\OtherProject\Proxies\__CG__\stdClass', 'stdClass'),
                array('MyProject\Proxies\__CG__\Symfony\Component\Security\Core\Tests\Util\ChildObject', 'Symfony\Component\Security\Core\Tests\Util\ChildObject'),
                array(new TestObject(), 'Symfony\Component\Security\Core\Tests\Util\TestObject'),
                array(new \Acme\DemoBundle\Proxy\__CG__\Symfony\Component\Security\Core\Tests\Util\TestObject(), 'Symfony\Component\Security\Core\Tests\Util\TestObject'),
            );
        }
        public function testGetRealClass($object, $expectedClassName)
        {
            $this->assertEquals($expectedClassName, ClassUtils::getRealClass($object));
        }
    }
    class TestObject
    {
    }
}
namespace Acme\DemoBundle\Proxy\__CG__\Symfony\Component\Security\Core\Tests\Util
{
    class TestObject extends \Symfony\Component\Security\Core\Tests\Util\TestObject
    {
    }
}
