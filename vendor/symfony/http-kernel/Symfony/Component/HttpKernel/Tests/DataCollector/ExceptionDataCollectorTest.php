<?php
namespace Symfony\Component\HttpKernel\Tests\DataCollector;
use Symfony\Component\Debug\Exception\FlattenException;
use Symfony\Component\HttpKernel\DataCollector\ExceptionDataCollector;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
class ExceptionDataCollectorTest extends \PHPUnit_Framework_TestCase
{
    public function testCollect()
    {
        $e = new \Exception('foo', 500);
        $c = new ExceptionDataCollector();
        $flattened = FlattenException::create($e);
        $trace = $flattened->getTrace();
        $this->assertFalse($c->hasException());
        $c->collect(new Request(), new Response(), $e);
        $this->assertTrue($c->hasException());
        $this->assertEquals($flattened, $c->getException());
        $this->assertSame('foo', $c->getMessage());
        $this->assertSame(500, $c->getCode());
        $this->assertSame('exception', $c->getName());
        $this->assertSame($trace, $c->getTrace());
    }
}
