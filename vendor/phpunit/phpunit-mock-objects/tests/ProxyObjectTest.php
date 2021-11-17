<?php
class Framework_ProxyObjectTest extends PHPUnit_Framework_TestCase
{
    public function testMockedMethodIsProxiedToOriginalMethod()
    {
        $proxy = $this->getMockBuilder('Bar')
                      ->enableProxyingToOriginalMethods()
                      ->getMock();
        $proxy->expects($this->once())
              ->method('doSomethingElse');
        $foo = new Foo;
        $this->assertEquals('result', $foo->doSomething($proxy));
    }
    public function testMockedMethodWithReferenceIsProxiedToOriginalMethod()
    {
        $proxy = $this->getMockBuilder('MethodCallbackByReference')
                      ->enableProxyingToOriginalMethods()
                      ->getMock();
        $a = $b = $c = 0;
        $proxy->callback($a, $b, $c);
        $this->assertEquals(1, $b);
    }
}
