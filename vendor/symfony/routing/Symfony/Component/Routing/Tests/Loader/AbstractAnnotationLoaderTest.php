<?php
namespace Symfony\Component\Routing\Tests\Loader;
abstract class AbstractAnnotationLoaderTest extends \PHPUnit_Framework_TestCase
{
    public function getReader()
    {
        return $this->getMockBuilder('Doctrine\Common\Annotations\Reader')
            ->disableOriginalConstructor()
            ->getMock()
        ;
    }
    public function getClassLoader($reader)
    {
        return $this->getMockBuilder('Symfony\Component\Routing\Loader\AnnotationClassLoader')
            ->setConstructorArgs(array($reader))
            ->getMockForAbstractClass()
        ;
    }
}
