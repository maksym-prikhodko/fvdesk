<?php
namespace PhpParser;
class DummyNode extends NodeAbstract {
    public $subNode1;
    public $subNode2;
    public function __construct($subNode1, $subNode2, $attributes) {
        parent::__construct(null, $attributes);
        $this->subNode1 = $subNode1;
        $this->subNode2 = $subNode2;
    }
    public function getSubNodeNames() {
        return array('subNode1', 'subNode2');
    }
    public function getType() {
        return 'Dummy';
    }
}
class NodeAbstractTest extends \PHPUnit_Framework_TestCase
{
    public function provideNodes() {
        $attributes = array(
            'startLine' => 10,
            'comments'  => array(
                new Comment('
                new Comment\Doc(''),
            ),
        );
        $node1 = $this->getMockForAbstractClass(
            'PhpParser\NodeAbstract',
            array(
                array(
                    'subNode1' => 'value1',
                    'subNode2' => 'value2',
                ),
                $attributes
            ),
            'PhpParser_Node_Dummy'
        );
        $node1->notSubNode = 'value3';
        $node2 = new DummyNode('value1', 'value2', $attributes);
        $node2->notSubNode = 'value3';
        return array(
            array($attributes, $node1),
            array($attributes, $node2),
        );
    }
    public function testConstruct(array $attributes, Node $node) {
        $this->assertSame('Dummy', $node->getType());
        $this->assertSame(array('subNode1', 'subNode2'), $node->getSubNodeNames());
        $this->assertSame(10, $node->getLine());
        $this->assertSame('', $node->getDocComment()->getText());
        $this->assertSame('value1', $node->subNode1);
        $this->assertSame('value2', $node->subNode2);
        $this->assertTrue(isset($node->subNode1));
        $this->assertTrue(isset($node->subNode2));
        $this->assertFalse(isset($node->subNode3));
        $this->assertSame($attributes, $node->getAttributes());
        return $node;
    }
    public function testGetDocComment(array $attributes, Node $node) {
        $this->assertSame('', $node->getDocComment()->getText());
        array_pop($node->getAttribute('comments')); 
        $this->assertNull($node->getDocComment());
        array_pop($node->getAttribute('comments')); 
        $this->assertNull($node->getDocComment());
    }
    public function testChange(array $attributes, Node $node) {
        $node->setLine(15);
        $this->assertSame(15, $node->getLine());
        $node->subNode = 'newValue';
        $this->assertSame('newValue', $node->subNode);
        $subNode =& $node->subNode;
        $subNode = 'newNewValue';
        $this->assertSame('newNewValue', $node->subNode);
        unset($node->subNode);
        $this->assertFalse(isset($node->subNode));
    }
    public function testIteration(array $attributes, Node $node) {
        $i = 0;
        foreach ($node as $key => $value) {
            if ($i === 0) {
                $this->assertSame('subNode1', $key);
                $this->assertSame('value1', $value);
            } else if ($i === 1) {
                $this->assertSame('subNode2', $key);
                $this->assertSame('value2', $value);
            } else if ($i === 2) {
                $this->assertSame('notSubNode', $key);
                $this->assertSame('value3', $value);
            } else {
                throw new \Exception;
            }
            $i++;
        }
        $this->assertSame(3, $i);
    }
    public function testAttributes() {
        $node = $this->getMockForAbstractClass('PhpParser\NodeAbstract');
        $this->assertEmpty($node->getAttributes());
        $node->setAttribute('key', 'value');
        $this->assertTrue($node->hasAttribute('key'));
        $this->assertSame('value', $node->getAttribute('key'));
        $this->assertFalse($node->hasAttribute('doesNotExist'));
        $this->assertNull($node->getAttribute('doesNotExist'));
        $this->assertSame('default', $node->getAttribute('doesNotExist', 'default'));
        $node->setAttribute('null', null);
        $this->assertTrue($node->hasAttribute('null'));
        $this->assertNull($node->getAttribute('null'));
        $this->assertNull($node->getAttribute('null', 'default'));
        $this->assertSame(
            array(
                'key'  => 'value',
                'null' => null,
            ),
            $node->getAttributes()
        );
    }
}
