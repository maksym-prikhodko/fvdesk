<?php
namespace Psy\Test\Util;
use Psy\Util\Docblock;
class DocblockTest extends \PHPUnit_Framework_TestCase
{
    public function testDocblockParsing($comment, $body, $tags)
    {
        $reflector = $this
            ->getMockBuilder('ReflectionClass')
            ->disableOriginalConstructor()
            ->getMock();
        $reflector->expects($this->once())
            ->method('getDocComment')
            ->will($this->returnValue($comment));
        $docblock = new Docblock($reflector);
        $this->assertEquals($body, $docblock->desc);
        foreach ($tags as $tag => $value) {
            $this->assertTrue($docblock->hasTag($tag));
            $this->assertEquals($value, $docblock->tag($tag));
        }
    }
    public function comments()
    {
        return array(
            array('', '', array()),
            array(
                '',
                "This is a docblock",
                array(
                    'throws' => array(array('type' => '\Exception', 'desc' => 'with a description')),
                ),
            ),
            array(
                '',
                'This is a slightly longer docblock',
                array(
                    'param' => array(
                        array('type' => 'int', 'desc' => 'Is a Foo', 'var' => '$foo'),
                        array('type' => 'string', 'desc' => 'With some sort of description', 'var' => '$bar'),
                        array('type' => '\ClassName', 'desc' => 'is cool too', 'var' => '$baz'),
                    ),
                    'return' => array(
                        array('type' => 'int', 'desc' => 'At least it isn\'t a string'),
                    ),
                ),
            ),
            array(
                '',
                "This is a docblock!\n\nIt spans lines, too!",
                array(
                    'tagname' => array('plus a description'),
                ),
            ),
        );
    }
}
