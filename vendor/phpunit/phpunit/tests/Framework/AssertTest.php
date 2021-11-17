<?php
class Framework_AssertTest extends PHPUnit_Framework_TestCase
{
    private $filesDirectory;
    protected function setUp()
    {
        $this->filesDirectory = dirname(__DIR__) . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR;
    }
    public function testFail()
    {
        try {
            $this->fail();
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }
        throw new PHPUnit_Framework_AssertionFailedError('Fail did not throw fail exception');
    }
    public function testAssertSplObjectStorageContainsObject()
    {
        $a = new stdClass;
        $b = new stdClass;
        $c = new SplObjectStorage;
        $c->attach($a);
        $this->assertContains($a, $c);
        try {
            $this->assertContains($b, $c);
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }
        $this->fail();
    }
    public function testAssertArrayContainsObject()
    {
        $a = new stdClass;
        $b = new stdClass;
        $this->assertContains($a, array($a));
        try {
            $this->assertContains($a, array($b));
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }
        $this->fail();
    }
    public function testAssertArrayContainsString()
    {
        $this->assertContains('foo', array('foo'));
        try {
            $this->assertContains('foo', array('bar'));
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }
        $this->fail();
    }
    public function testAssertArrayContainsNonObject()
    {
        $this->assertContains('foo', array(true));
        try {
            $this->assertContains('foo', array(true), '', false, true, true);
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }
        $this->fail();
    }
    public function testAssertContainsOnlyInstancesOf()
    {
        $test = array(
            new Book(),
            new Book
        );
        $this->assertContainsOnlyInstancesOf('Book', $test);
        $this->assertContainsOnlyInstancesOf('stdClass', array(new stdClass()));
        $test2 = array(
            new Author('Test')
        );
        try {
            $this->assertContainsOnlyInstancesOf('Book', $test2);
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }
        $this->fail();
    }
    public function testAssertArrayHasKeyThrowsExceptionForInvalidFirstArgument()
    {
        $this->assertArrayHasKey(null, array());
    }
    public function testAssertArrayHasKeyThrowsExceptionForInvalidSecondArgument()
    {
        $this->assertArrayHasKey(0, null);
    }
    public function testAssertArrayHasIntegerKey()
    {
        $this->assertArrayHasKey(0, array('foo'));
        try {
            $this->assertArrayHasKey(1, array('foo'));
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }
        $this->fail();
    }
    public function testassertArraySubset()
    {
        $array = array(
            'a' => 'item a',
            'b' => 'item b',
            'c' => array('a2' => 'item a2', 'b2' => 'item b2'),
            'd' => array('a2' => array('a3' => 'item a3', 'b3' => 'item b3'))
        );
        $this->assertArraySubset(array('a' => 'item a', 'c' => array('a2' => 'item a2')), $array);
        $this->assertArraySubset(array('a' => 'item a', 'd' => array('a2' => array('b3' => 'item b3'))), $array);
        try {
            $this->assertArraySubset(array('a' => 'bad value'), $array);
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
        }
        try {
            $this->assertArraySubset(array('d' => array('a2' => array('bad index' => 'item b3'))), $array);
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }
        $this->fail();
    }
    public function testassertArraySubsetWithDeepNestedArrays()
    {
        $array = array(
            'path' => array(
                'to' => array(
                    'the' => array(
                        'cake' => 'is a lie'
                    )
                )
            )
        );
        $this->assertArraySubset(array('path' => array()), $array);
        $this->assertArraySubset(array('path' => array('to' => array())), $array);
        $this->assertArraySubset(array('path' => array('to' => array('the' => array()))), $array);
        $this->assertArraySubset(array('path' => array('to' => array('the' => array('cake' => 'is a lie')))), $array);
        try {
            $this->assertArraySubset(array('path' => array('to' => array('the' => array('cake' => 'is not a lie')))), $array);
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }
        $this->fail();
    }
    public function testassertArraySubsetWithNoStrictCheckAndObjects()
    {
        $obj = new \stdClass;
        $reference = &$obj;
        $array = array('a' => $obj);
        $this->assertArraySubset(array('a' => $reference), $array);
        $this->assertArraySubset(array('a' => new \stdClass), $array);
    }
    public function testassertArraySubsetWithStrictCheckAndObjects()
    {
        $obj = new \stdClass;
        $reference = &$obj;
        $array = array('a' => $obj);
        $this->assertArraySubset(array('a' => $reference), $array, true);
        try {
            $this->assertArraySubset(array('a' => new \stdClass), $array, true);
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }
        $this->fail('Strict recursive array check fail.');
    }
    public function testassertArraySubsetRaisesExceptionForInvalidArguments($partial, $subject)
    {
        $this->assertArraySubset($partial, $subject);
    }
    public function assertArraySubsetInvalidArgumentProvider()
    {
        return array(
            array(false, array()),
            array(array(), false),
        );
    }
    public function testAssertArrayNotHasKeyThrowsExceptionForInvalidFirstArgument()
    {
        $this->assertArrayNotHasKey(null, array());
    }
    public function testAssertArrayNotHasKeyThrowsExceptionForInvalidSecondArgument()
    {
        $this->assertArrayNotHasKey(0, null);
    }
    public function testAssertArrayNotHasIntegerKey()
    {
        $this->assertArrayNotHasKey(1, array('foo'));
        try {
            $this->assertArrayNotHasKey(0, array('foo'));
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }
        $this->fail();
    }
    public function testAssertArrayHasStringKey()
    {
        $this->assertArrayHasKey('foo', array('foo' => 'bar'));
        try {
            $this->assertArrayHasKey('bar', array('foo' => 'bar'));
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }
        $this->fail();
    }
    public function testAssertArrayNotHasStringKey()
    {
        $this->assertArrayNotHasKey('bar', array('foo' => 'bar'));
        try {
            $this->assertArrayNotHasKey('foo', array('foo' => 'bar'));
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }
        $this->fail();
    }
    public function testAssertArrayHasKeyAcceptsArrayObjectValue()
    {
        $array = new ArrayObject();
        $array['foo'] = 'bar';
        $this->assertArrayHasKey('foo', $array);
    }
    public function testAssertArrayHasKeyProperlyFailsWithArrayObjectValue()
    {
        $array = new ArrayObject();
        $array['bar'] = 'bar';
        $this->assertArrayHasKey('foo', $array);
    }
    public function testAssertArrayHasKeyAcceptsArrayAccessValue()
    {
        $array = new SampleArrayAccess();
        $array['foo'] = 'bar';
        $this->assertArrayHasKey('foo', $array);
    }
    public function testAssertArrayHasKeyProperlyFailsWithArrayAccessValue()
    {
        $array = new SampleArrayAccess();
        $array['bar'] = 'bar';
        $this->assertArrayHasKey('foo', $array);
    }
    public function testAssertArrayNotHasKeyAcceptsArrayAccessValue()
    {
        $array = new ArrayObject();
        $array['foo'] = 'bar';
        $this->assertArrayNotHasKey('bar', $array);
    }
    public function testAssertArrayNotHasKeyPropertlyFailsWithArrayAccessValue()
    {
        $array = new ArrayObject();
        $array['bar'] = 'bar';
        $this->assertArrayNotHasKey('bar', $array);
    }
    public function testAssertContainsThrowsException()
    {
        $this->assertContains(null, null);
    }
    public function testAssertIteratorContainsObject()
    {
        $foo = new stdClass;
        $this->assertContains($foo, new TestIterator(array($foo)));
        try {
            $this->assertContains($foo, new TestIterator(array(new stdClass)));
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }
        $this->fail();
    }
    public function testAssertIteratorContainsString()
    {
        $this->assertContains('foo', new TestIterator(array('foo')));
        try {
            $this->assertContains('foo', new TestIterator(array('bar')));
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }
        $this->fail();
    }
    public function testAssertStringContainsString()
    {
        $this->assertContains('foo', 'foobar');
        try {
            $this->assertContains('foo', 'bar');
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }
        $this->fail();
    }
    public function testAssertNotContainsThrowsException()
    {
        $this->assertNotContains(null, null);
    }
    public function testAssertSplObjectStorageNotContainsObject()
    {
        $a = new stdClass;
        $b = new stdClass;
        $c = new SplObjectStorage;
        $c->attach($a);
        $this->assertNotContains($b, $c);
        try {
            $this->assertNotContains($a, $c);
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }
        $this->fail();
    }
    public function testAssertArrayNotContainsObject()
    {
        $a = new stdClass;
        $b = new stdClass;
        $this->assertNotContains($a, array($b));
        try {
            $this->assertNotContains($a, array($a));
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }
        $this->fail();
    }
    public function testAssertArrayNotContainsString()
    {
        $this->assertNotContains('foo', array('bar'));
        try {
            $this->assertNotContains('foo', array('foo'));
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }
        $this->fail();
    }
    public function testAssertArrayNotContainsNonObject()
    {
        $this->assertNotContains('foo', array(true), '', false, true, true);
        try {
            $this->assertNotContains('foo', array(true));
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }
        $this->fail();
    }
    public function testAssertStringNotContainsString()
    {
        $this->assertNotContains('foo', 'bar');
        try {
            $this->assertNotContains('foo', 'foo');
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }
        $this->fail();
    }
    public function testAssertContainsOnlyThrowsException()
    {
        $this->assertContainsOnly(null, null);
    }
    public function testAssertNotContainsOnlyThrowsException()
    {
        $this->assertNotContainsOnly(null, null);
    }
    public function testAssertContainsOnlyInstancesOfThrowsException()
    {
        $this->assertContainsOnlyInstancesOf(null, null);
    }
    public function testAssertArrayContainsOnlyIntegers()
    {
        $this->assertContainsOnly('integer', array(1, 2, 3));
        try {
            $this->assertContainsOnly('integer', array("1", 2, 3));
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }
        $this->fail();
    }
    public function testAssertArrayNotContainsOnlyIntegers()
    {
        $this->assertNotContainsOnly('integer', array("1", 2, 3));
        try {
            $this->assertNotContainsOnly('integer', array(1, 2, 3));
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }
        $this->fail();
    }
    public function testAssertArrayContainsOnlyStdClass()
    {
        $this->assertContainsOnly('StdClass', array(new StdClass));
        try {
            $this->assertContainsOnly('StdClass', array('StdClass'));
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }
        $this->fail();
    }
    public function testAssertArrayNotContainsOnlyStdClass()
    {
        $this->assertNotContainsOnly('StdClass', array('StdClass'));
        try {
            $this->assertNotContainsOnly('StdClass', array(new StdClass));
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }
        $this->fail();
    }
    protected function createDOMDocument($content)
    {
        $document = new DOMDocument;
        $document->preserveWhiteSpace = false;
        $document->loadXML($content);
        return $document;
    }
    protected function sameValues()
    {
        $object = new SampleClass(4, 8, 15);
        $file = dirname(__DIR__) . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'foo.xml';
        $resource = fopen($file, 'r');
        return array(
            array(null, null),
            array('a', 'a'),
            array(0, 0),
            array(2.3, 2.3),
            array(1/3, 1 - 2/3),
            array(log(0), log(0)),
            array(array(), array()),
            array(array(0 => 1), array(0 => 1)),
            array(array(0 => null), array(0 => null)),
            array(array('a', 'b' => array(1, 2)), array('a', 'b' => array(1, 2))),
            array($object, $object),
            array($resource, $resource),
        );
    }
    protected function notEqualValues()
    {
        $book1 = new Book;
        $book1->author = new Author('Terry Pratchett');
        $book1->author->books[] = $book1;
        $book2 = new Book;
        $book2->author = new Author('Terry Pratch');
        $book2->author->books[] = $book2;
        $book3 = new Book;
        $book3->author = 'Terry Pratchett';
        $book4 = new stdClass;
        $book4->author = 'Terry Pratchett';
        $object1 = new SampleClass(4, 8, 15);
        $object2 = new SampleClass(16, 23, 42);
        $object3 = new SampleClass(4, 8, 15);
        $storage1 = new SplObjectStorage;
        $storage1->attach($object1);
        $storage2 = new SplObjectStorage;
        $storage2->attach($object3); 
        $file = dirname(__DIR__) . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'foo.xml';
        return array(
            array('a', 'b'),
            array('a', 'A'),
            array('9E6666666','9E7777777'),
            array(1, 2),
            array(2, 1),
            array(2.3, 4.2),
            array(2.3, 4.2, 0.5),
            array(array(2.3), array(4.2), 0.5),
            array(array(array(2.3)), array(array(4.2)), 0.5),
            array(new Struct(2.3), new Struct(4.2), 0.5),
            array(array(new Struct(2.3)), array(new Struct(4.2)), 0.5),
            array(NAN, NAN),
            array(array(), array(0 => 1)),
            array(array(0 => 1), array()),
            array(array(0 => null), array()),
            array(array(0 => 1, 1 => 2), array(0 => 1, 1 => 3)),
            array(array('a', 'b' => array(1, 2)), array('a', 'b' => array(2, 1))),
            array(new SampleClass(4, 8, 15), new SampleClass(16, 23, 42)),
            array($object1, $object2),
            array($book1, $book2),
            array($book3, $book4), 
            array(fopen($file, 'r'), fopen($file, 'r')),
            array($storage1, $storage2),
            array(
                $this->createDOMDocument('<root></root>'),
                $this->createDOMDocument('<bar/>'),
            ),
            array(
                $this->createDOMDocument('<foo attr1="bar"/>'),
                $this->createDOMDocument('<foo attr1="foobar"/>'),
            ),
            array(
                $this->createDOMDocument('<foo> bar </foo>'),
                $this->createDOMDocument('<foo />'),
            ),
            array(
                $this->createDOMDocument('<foo xmlns="urn:myns:bar"/>'),
                $this->createDOMDocument('<foo xmlns="urn:notmyns:bar"/>'),
            ),
            array(
                $this->createDOMDocument('<foo> bar </foo>'),
                $this->createDOMDocument('<foo> bir </foo>'),
            ),
            array(
                new DateTime('2013-03-29 04:13:35', new DateTimeZone('America/New_York')),
                new DateTime('2013-03-29 03:13:35', new DateTimeZone('America/New_York')),
            ),
            array(
                new DateTime('2013-03-29 04:13:35', new DateTimeZone('America/New_York')),
                new DateTime('2013-03-29 03:13:35', new DateTimeZone('America/New_York')),
                3500
            ),
            array(
                new DateTime('2013-03-29 04:13:35', new DateTimeZone('America/New_York')),
                new DateTime('2013-03-29 05:13:35', new DateTimeZone('America/New_York')),
                3500
            ),
            array(
                new DateTime('2013-03-29', new DateTimeZone('America/New_York')),
                new DateTime('2013-03-30', new DateTimeZone('America/New_York')),
            ),
            array(
                new DateTime('2013-03-29', new DateTimeZone('America/New_York')),
                new DateTime('2013-03-30', new DateTimeZone('America/New_York')),
                43200
            ),
            array(
                new DateTime('2013-03-29 04:13:35', new DateTimeZone('America/New_York')),
                new DateTime('2013-03-29 04:13:35', new DateTimeZone('America/Chicago')),
            ),
            array(
                new DateTime('2013-03-29 04:13:35', new DateTimeZone('America/New_York')),
                new DateTime('2013-03-29 04:13:35', new DateTimeZone('America/Chicago')),
                3500
            ),
            array(
                new DateTime('2013-03-30', new DateTimeZone('America/New_York')),
                new DateTime('2013-03-30', new DateTimeZone('America/Chicago')),
            ),
            array(
                new DateTime('2013-03-29T05:13:35-0600'),
                new DateTime('2013-03-29T04:13:35-0600'),
            ),
            array(
                new DateTime('2013-03-29T05:13:35-0600'),
                new DateTime('2013-03-29T05:13:35-0500'),
            ),
            array(new SampleClass(4, 8, 15), false),
            array(false, new SampleClass(4, 8, 15)),
            array(array(0 => 1, 1 => 2), false),
            array(false, array(0 => 1, 1 => 2)),
            array(array(), new stdClass),
            array(new stdClass, array()),
            array(0, 'Foobar'),
            array('Foobar', 0),
            array(3, acos(8)),
            array(acos(8), 3)
        );
    }
    protected function equalValues()
    {
        $book1 = new Book;
        $book1->author = new Author('Terry Pratchett');
        $book1->author->books[] = $book1;
        $book2 = new Book;
        $book2->author = new Author('Terry Pratchett');
        $book2->author->books[] = $book2;
        $object1 = new SampleClass(4, 8, 15);
        $object2 = new SampleClass(4, 8, 15);
        $storage1 = new SplObjectStorage;
        $storage1->attach($object1);
        $storage2 = new SplObjectStorage;
        $storage2->attach($object1);
        return array(
            array('a', 'A', 0, false, true), 
            array(array('a' => 1, 'b' => 2), array('b' => 2, 'a' => 1)),
            array(array(1), array('1')),
            array(array(3, 2, 1), array(2, 3, 1), 0, true), 
            array(2.3, 2.5, 0.5),
            array(array(2.3), array(2.5), 0.5),
            array(array(array(2.3)), array(array(2.5)), 0.5),
            array(new Struct(2.3), new Struct(2.5), 0.5),
            array(array(new Struct(2.3)), array(new Struct(2.5)), 0.5),
            array(1, 2, 1),
            array($object1, $object2),
            array($book1, $book2),
            array($storage1, $storage2),
            array(
                $this->createDOMDocument('<root></root>'),
                $this->createDOMDocument('<root/>'),
            ),
            array(
                $this->createDOMDocument('<root attr="bar"></root>'),
                $this->createDOMDocument('<root attr="bar"/>'),
            ),
            array(
                $this->createDOMDocument('<root><foo attr="bar"></foo></root>'),
                $this->createDOMDocument('<root><foo attr="bar"/></root>'),
            ),
            array(
                $this->createDOMDocument("<root>\n  <child/>\n</root>"),
                $this->createDOMDocument('<root><child/></root>'),
            ),
            array(
                new DateTime('2013-03-29 04:13:35', new DateTimeZone('America/New_York')),
                new DateTime('2013-03-29 04:13:35', new DateTimeZone('America/New_York')),
            ),
            array(
                new DateTime('2013-03-29 04:13:35', new DateTimeZone('America/New_York')),
                new DateTime('2013-03-29 04:13:25', new DateTimeZone('America/New_York')),
                10
            ),
            array(
                new DateTime('2013-03-29 04:13:35', new DateTimeZone('America/New_York')),
                new DateTime('2013-03-29 04:14:40', new DateTimeZone('America/New_York')),
                65
            ),
            array(
                new DateTime('2013-03-29', new DateTimeZone('America/New_York')),
                new DateTime('2013-03-29', new DateTimeZone('America/New_York')),
            ),
            array(
                new DateTime('2013-03-29 04:13:35', new DateTimeZone('America/New_York')),
                new DateTime('2013-03-29 03:13:35', new DateTimeZone('America/Chicago')),
            ),
            array(
                new DateTime('2013-03-29 04:13:35', new DateTimeZone('America/New_York')),
                new DateTime('2013-03-29 03:13:49', new DateTimeZone('America/Chicago')),
                15
            ),
            array(
                new DateTime('2013-03-30', new DateTimeZone('America/New_York')),
                new DateTime('2013-03-29 23:00:00', new DateTimeZone('America/Chicago')),
            ),
            array(
                new DateTime('2013-03-30', new DateTimeZone('America/New_York')),
                new DateTime('2013-03-29 23:01:30', new DateTimeZone('America/Chicago')),
                100
            ),
            array(
                new DateTime('@1364616000'),
                new DateTime('2013-03-29 23:00:00', new DateTimeZone('America/Chicago')),
            ),
            array(
                new DateTime('2013-03-29T05:13:35-0500'),
                new DateTime('2013-03-29T04:13:35-0600'),
            ),
            array(0, '0'),
            array('0', 0),
            array(2.3, '2.3'),
            array('2.3', 2.3),
            array((string)(1/3), 1 - 2/3),
            array(1/3, (string)(1 - 2/3)),
            array('string representation', new ClassWithToString),
            array(new ClassWithToString, 'string representation'),
        );
    }
    public function equalProvider()
    {
        return array_merge($this->equalValues(), $this->sameValues());
    }
    public function notEqualProvider()
    {
        return $this->notEqualValues();
    }
    public function sameProvider()
    {
        return $this->sameValues();
    }
    public function notSameProvider()
    {
        return array_merge($this->notEqualValues(), $this->equalValues());
    }
    public function testAssertEqualsSucceeds($a, $b, $delta = 0.0, $canonicalize = false, $ignoreCase = false)
    {
        $this->assertEquals($a, $b, '', $delta, 10, $canonicalize, $ignoreCase);
    }
    public function testAssertEqualsFails($a, $b, $delta = 0.0, $canonicalize = false, $ignoreCase = false)
    {
        try {
            $this->assertEquals($a, $b, '', $delta, 10, $canonicalize, $ignoreCase);
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }
        $this->fail();
    }
    public function testAssertNotEqualsSucceeds($a, $b, $delta = 0.0, $canonicalize = false, $ignoreCase = false)
    {
        $this->assertNotEquals($a, $b, '', $delta, 10, $canonicalize, $ignoreCase);
    }
    public function testAssertNotEqualsFails($a, $b, $delta = 0.0, $canonicalize = false, $ignoreCase = false)
    {
        try {
            $this->assertNotEquals($a, $b, '', $delta, 10, $canonicalize, $ignoreCase);
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }
        $this->fail();
    }
    public function testAssertSameSucceeds($a, $b)
    {
        $this->assertSame($a, $b);
    }
    public function testAssertSameFails($a, $b)
    {
        try {
            $this->assertSame($a, $b);
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }
        $this->fail();
    }
    public function testAssertNotSameSucceeds($a, $b)
    {
        $this->assertNotSame($a, $b);
    }
    public function testAssertNotSameFails($a, $b)
    {
        try {
            $this->assertNotSame($a, $b);
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }
        $this->fail();
    }
    public function testAssertXmlFileEqualsXmlFile()
    {
        $this->assertXmlFileEqualsXmlFile(
            $this->filesDirectory . 'foo.xml',
            $this->filesDirectory . 'foo.xml'
        );
        try {
            $this->assertXmlFileEqualsXmlFile(
                $this->filesDirectory . 'foo.xml',
                $this->filesDirectory . 'bar.xml'
            );
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }
        $this->fail();
    }
    public function testAssertXmlFileNotEqualsXmlFile()
    {
        $this->assertXmlFileNotEqualsXmlFile(
            $this->filesDirectory . 'foo.xml',
            $this->filesDirectory . 'bar.xml'
        );
        try {
            $this->assertXmlFileNotEqualsXmlFile(
                $this->filesDirectory . 'foo.xml',
                $this->filesDirectory . 'foo.xml'
            );
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }
        $this->fail();
    }
    public function testAssertXmlStringEqualsXmlFile()
    {
        $this->assertXmlStringEqualsXmlFile(
            $this->filesDirectory . 'foo.xml',
            file_get_contents($this->filesDirectory . 'foo.xml')
        );
        try {
            $this->assertXmlStringEqualsXmlFile(
                $this->filesDirectory . 'foo.xml',
                file_get_contents($this->filesDirectory . 'bar.xml')
            );
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }
        $this->fail();
    }
    public function testXmlStringNotEqualsXmlFile()
    {
        $this->assertXmlStringNotEqualsXmlFile(
            $this->filesDirectory . 'foo.xml',
            file_get_contents($this->filesDirectory . 'bar.xml')
        );
        try {
            $this->assertXmlStringNotEqualsXmlFile(
                $this->filesDirectory . 'foo.xml',
                file_get_contents($this->filesDirectory . 'foo.xml')
            );
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }
        $this->fail();
    }
    public function testAssertXmlStringEqualsXmlString()
    {
        $this->assertXmlStringEqualsXmlString('<root/>', '<root/>');
        try {
            $this->assertXmlStringEqualsXmlString('<foo/>', '<bar/>');
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }
        $this->fail();
    }
    public function testAssertXmlStringNotEqualsXmlString()
    {
        $this->assertXmlStringNotEqualsXmlString('<foo/>', '<bar/>');
        try {
            $this->assertXmlStringNotEqualsXmlString('<root/>', '<root/>');
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }
        $this->fail();
    }
    public function testXMLStructureIsSame()
    {
        $expected = new DOMDocument;
        $expected->load($this->filesDirectory . 'structureExpected.xml');
        $actual = new DOMDocument;
        $actual->load($this->filesDirectory . 'structureExpected.xml');
        $this->assertEqualXMLStructure(
            $expected->firstChild, $actual->firstChild, true
        );
    }
    public function testXMLStructureWrongNumberOfAttributes()
    {
        $expected = new DOMDocument;
        $expected->load($this->filesDirectory . 'structureExpected.xml');
        $actual = new DOMDocument;
        $actual->load($this->filesDirectory . 'structureWrongNumberOfAttributes.xml');
        $this->assertEqualXMLStructure(
            $expected->firstChild, $actual->firstChild, true
        );
    }
    public function testXMLStructureWrongNumberOfNodes()
    {
        $expected = new DOMDocument;
        $expected->load($this->filesDirectory . 'structureExpected.xml');
        $actual = new DOMDocument;
        $actual->load($this->filesDirectory . 'structureWrongNumberOfNodes.xml');
        $this->assertEqualXMLStructure(
            $expected->firstChild, $actual->firstChild, true
        );
    }
    public function testXMLStructureIsSameButDataIsNot()
    {
        $expected = new DOMDocument;
        $expected->load($this->filesDirectory . 'structureExpected.xml');
        $actual = new DOMDocument;
        $actual->load($this->filesDirectory . 'structureIsSameButDataIsNot.xml');
        $this->assertEqualXMLStructure(
            $expected->firstChild, $actual->firstChild, true
        );
    }
    public function testXMLStructureAttributesAreSameButValuesAreNot()
    {
        $expected = new DOMDocument;
        $expected->load($this->filesDirectory . 'structureExpected.xml');
        $actual = new DOMDocument;
        $actual->load($this->filesDirectory . 'structureAttributesAreSameButValuesAreNot.xml');
        $this->assertEqualXMLStructure(
            $expected->firstChild, $actual->firstChild, true
        );
    }
    public function testXMLStructureIgnoreTextNodes()
    {
        $expected = new DOMDocument;
        $expected->load($this->filesDirectory . 'structureExpected.xml');
        $actual = new DOMDocument;
        $actual->load($this->filesDirectory . 'structureIgnoreTextNodes.xml');
        $this->assertEqualXMLStructure(
            $expected->firstChild, $actual->firstChild, true
        );
    }
    public function testAssertStringEqualsNumeric()
    {
        $this->assertEquals('0', 0);
        try {
            $this->assertEquals('0', 1);
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }
        $this->fail();
    }
    public function testAssertStringEqualsNumeric2()
    {
        $this->assertNotEquals('A', 0);
    }
    public function testAssertFileExistsThrowsException()
    {
        $this->assertFileExists(null);
    }
    public function testAssertFileExists()
    {
        $this->assertFileExists(__FILE__);
        try {
            $this->assertFileExists(__DIR__ . DIRECTORY_SEPARATOR . 'NotExisting');
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }
        $this->fail();
    }
    public function testAssertFileNotExistsThrowsException()
    {
        $this->assertFileNotExists(null);
    }
    public function testAssertFileNotExists()
    {
        $this->assertFileNotExists(__DIR__ . DIRECTORY_SEPARATOR . 'NotExisting');
        try {
            $this->assertFileNotExists(__FILE__);
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }
        $this->fail();
    }
    public function testAssertObjectHasAttribute()
    {
        $o = new Author('Terry Pratchett');
        $this->assertObjectHasAttribute('name', $o);
        try {
            $this->assertObjectHasAttribute('foo', $o);
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }
        $this->fail();
    }
    public function testAssertObjectNotHasAttribute()
    {
        $o = new Author('Terry Pratchett');
        $this->assertObjectNotHasAttribute('foo', $o);
        try {
            $this->assertObjectNotHasAttribute('name', $o);
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }
        $this->fail();
    }
    public function testAssertNull()
    {
        $this->assertNull(null);
        try {
            $this->assertNull(new stdClass);
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }
        $this->fail();
    }
    public function testAssertNotNull()
    {
        $this->assertNotNull(new stdClass);
        try {
            $this->assertNotNull(null);
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }
        $this->fail();
    }
    public function testAssertTrue()
    {
        $this->assertTrue(true);
        try {
            $this->assertTrue(false);
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }
        $this->fail();
    }
    public function testAssertNotTrue()
    {
        $this->assertNotTrue(false);
        $this->assertNotTrue(1);
        $this->assertNotTrue("true");
        try {
            $this->assertNotTrue(true);
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }
        $this->fail();
    }
    public function testAssertFalse()
    {
        $this->assertFalse(false);
        try {
            $this->assertFalse(true);
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }
        $this->fail();
    }
    public function testAssertNotFalse()
    {
        $this->assertNotFalse(true);
        $this->assertNotFalse(0);
        $this->assertNotFalse("");
        try {
            $this->assertNotFalse(false);
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }
        $this->fail();
    }
    public function testAssertRegExpThrowsException()
    {
        $this->assertRegExp(null, null);
    }
    public function testAssertRegExpThrowsException2()
    {
        $this->assertRegExp('', null);
    }
    public function testAssertNotRegExpThrowsException()
    {
        $this->assertNotRegExp(null, null);
    }
    public function testAssertNotRegExpThrowsException2()
    {
        $this->assertNotRegExp('', null);
    }
    public function testAssertRegExp()
    {
        $this->assertRegExp('/foo/', 'foobar');
        try {
            $this->assertRegExp('/foo/', 'bar');
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }
        $this->fail();
    }
    public function testAssertNotRegExp()
    {
        $this->assertNotRegExp('/foo/', 'bar');
        try {
            $this->assertNotRegExp('/foo/', 'foobar');
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }
        $this->fail();
    }
    public function testAssertSame()
    {
        $o = new stdClass;
        $this->assertSame($o, $o);
        try {
            $this->assertSame(
                new stdClass,
                new stdClass
            );
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }
        $this->fail();
    }
    public function testAssertSame2()
    {
        $this->assertSame(true, true);
        $this->assertSame(false, false);
        try {
            $this->assertSame(true, false);
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }
        $this->fail();
    }
    public function testAssertNotSame()
    {
        $this->assertNotSame(
            new stdClass,
            null
        );
        $this->assertNotSame(
            null,
            new stdClass
        );
        $this->assertNotSame(
            new stdClass,
            new stdClass
        );
        $o = new stdClass;
        try {
            $this->assertNotSame($o, $o);
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }
        $this->fail();
    }
    public function testAssertNotSame2()
    {
        $this->assertNotSame(true, false);
        $this->assertNotSame(false, true);
        try {
            $this->assertNotSame(true, true);
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }
        $this->fail();
    }
    public function testAssertNotSameFailsNull()
    {
        try {
            $this->assertNotSame(null, null);
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }
        $this->fail();
    }
    public function testGreaterThan()
    {
        $this->assertGreaterThan(1, 2);
        try {
            $this->assertGreaterThan(2, 1);
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }
        $this->fail();
    }
    public function testAttributeGreaterThan()
    {
        $this->assertAttributeGreaterThan(
            1, 'bar', new ClassWithNonPublicAttributes
        );
        try {
            $this->assertAttributeGreaterThan(
                1, 'foo', new ClassWithNonPublicAttributes
            );
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }
        $this->fail();
    }
    public function testGreaterThanOrEqual()
    {
        $this->assertGreaterThanOrEqual(1, 2);
        try {
            $this->assertGreaterThanOrEqual(2, 1);
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }
        $this->fail();
    }
    public function testAttributeGreaterThanOrEqual()
    {
        $this->assertAttributeGreaterThanOrEqual(
            1, 'bar', new ClassWithNonPublicAttributes
        );
        try {
            $this->assertAttributeGreaterThanOrEqual(
                2, 'foo', new ClassWithNonPublicAttributes
            );
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }
        $this->fail();
    }
    public function testLessThan()
    {
        $this->assertLessThan(2, 1);
        try {
            $this->assertLessThan(1, 2);
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }
        $this->fail();
    }
    public function testAttributeLessThan()
    {
        $this->assertAttributeLessThan(
            2, 'foo', new ClassWithNonPublicAttributes
        );
        try {
            $this->assertAttributeLessThan(
                1, 'bar', new ClassWithNonPublicAttributes
            );
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }
        $this->fail();
    }
    public function testLessThanOrEqual()
    {
        $this->assertLessThanOrEqual(2, 1);
        try {
            $this->assertLessThanOrEqual(1, 2);
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }
        $this->fail();
    }
    public function testAttributeLessThanOrEqual()
    {
        $this->assertAttributeLessThanOrEqual(
            2, 'foo', new ClassWithNonPublicAttributes
        );
        try {
            $this->assertAttributeLessThanOrEqual(
                1, 'bar', new ClassWithNonPublicAttributes
            );
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }
        $this->fail();
    }
    public function testReadAttribute()
    {
        $obj = new ClassWithNonPublicAttributes;
        $this->assertEquals('foo', $this->readAttribute($obj, 'publicAttribute'));
        $this->assertEquals('bar', $this->readAttribute($obj, 'protectedAttribute'));
        $this->assertEquals('baz', $this->readAttribute($obj, 'privateAttribute'));
        $this->assertEquals('bar', $this->readAttribute($obj, 'protectedParentAttribute'));
    }
    public function testReadAttribute2()
    {
        $this->assertEquals('foo', $this->readAttribute('ClassWithNonPublicAttributes', 'publicStaticAttribute'));
        $this->assertEquals('bar', $this->readAttribute('ClassWithNonPublicAttributes', 'protectedStaticAttribute'));
        $this->assertEquals('baz', $this->readAttribute('ClassWithNonPublicAttributes', 'privateStaticAttribute'));
        $this->assertEquals('foo', $this->readAttribute('ClassWithNonPublicAttributes', 'protectedStaticParentAttribute'));
        $this->assertEquals('foo', $this->readAttribute('ClassWithNonPublicAttributes', 'privateStaticParentAttribute'));
    }
    public function testReadAttribute3()
    {
        $this->readAttribute('StdClass', null);
    }
    public function testReadAttribute4()
    {
        $this->readAttribute('NotExistingClass', 'foo');
    }
    public function testReadAttribute5()
    {
        $this->readAttribute(null, 'foo');
    }
    public function testReadAttributeIfAttributeNameIsNotValid()
    {
        $this->readAttribute('StdClass', '2');
    }
    public function testGetStaticAttributeRaisesExceptionForInvalidFirstArgument()
    {
        $this->getStaticAttribute(null, 'foo');
    }
    public function testGetStaticAttributeRaisesExceptionForInvalidFirstArgument2()
    {
        $this->getStaticAttribute('NotExistingClass', 'foo');
    }
    public function testGetStaticAttributeRaisesExceptionForInvalidSecondArgument()
    {
        $this->getStaticAttribute('stdClass', null);
    }
    public function testGetStaticAttributeRaisesExceptionForInvalidSecondArgument2()
    {
        $this->getStaticAttribute('stdClass', '0');
    }
    public function testGetStaticAttributeRaisesExceptionForInvalidSecondArgument3()
    {
        $this->getStaticAttribute('stdClass', 'foo');
    }
    public function testGetObjectAttributeRaisesExceptionForInvalidFirstArgument()
    {
        $this->getObjectAttribute(null, 'foo');
    }
    public function testGetObjectAttributeRaisesExceptionForInvalidSecondArgument()
    {
        $this->getObjectAttribute(new stdClass, null);
    }
    public function testGetObjectAttributeRaisesExceptionForInvalidSecondArgument2()
    {
        $this->getObjectAttribute(new stdClass, '0');
    }
    public function testGetObjectAttributeRaisesExceptionForInvalidSecondArgument3()
    {
        $this->getObjectAttribute(new stdClass, 'foo');
    }
    public function testGetObjectAttributeWorksForInheritedAttributes()
    {
        $this->assertEquals(
            'bar',
            $this->getObjectAttribute(new ClassWithNonPublicAttributes, 'privateParentAttribute')
        );
    }
    public function testAssertPublicAttributeContains()
    {
        $obj = new ClassWithNonPublicAttributes;
        $this->assertAttributeContains('foo', 'publicArray', $obj);
        try {
            $this->assertAttributeContains('bar', 'publicArray', $obj);
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }
        $this->fail();
    }
    public function testAssertPublicAttributeContainsOnly()
    {
        $obj = new ClassWithNonPublicAttributes;
        $this->assertAttributeContainsOnly('string', 'publicArray', $obj);
        try {
            $this->assertAttributeContainsOnly('integer', 'publicArray', $obj);
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }
        $this->fail();
    }
    public function testAssertPublicAttributeNotContains()
    {
        $obj = new ClassWithNonPublicAttributes;
        $this->assertAttributeNotContains('bar', 'publicArray', $obj);
        try {
            $this->assertAttributeNotContains('foo', 'publicArray', $obj);
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }
        $this->fail();
    }
    public function testAssertPublicAttributeNotContainsOnly()
    {
        $obj = new ClassWithNonPublicAttributes;
        $this->assertAttributeNotContainsOnly('integer', 'publicArray', $obj);
        try {
            $this->assertAttributeNotContainsOnly('string', 'publicArray', $obj);
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }
        $this->fail();
    }
    public function testAssertProtectedAttributeContains()
    {
        $obj = new ClassWithNonPublicAttributes;
        $this->assertAttributeContains('bar', 'protectedArray', $obj);
        try {
            $this->assertAttributeContains('foo', 'protectedArray', $obj);
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }
        $this->fail();
    }
    public function testAssertProtectedAttributeNotContains()
    {
        $obj = new ClassWithNonPublicAttributes;
        $this->assertAttributeNotContains('foo', 'protectedArray', $obj);
        try {
            $this->assertAttributeNotContains('bar', 'protectedArray', $obj);
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }
        $this->fail();
    }
    public function testAssertPrivateAttributeContains()
    {
        $obj = new ClassWithNonPublicAttributes;
        $this->assertAttributeContains('baz', 'privateArray', $obj);
        try {
            $this->assertAttributeContains('foo', 'privateArray', $obj);
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }
        $this->fail();
    }
    public function testAssertPrivateAttributeNotContains()
    {
        $obj = new ClassWithNonPublicAttributes;
        $this->assertAttributeNotContains('foo', 'privateArray', $obj);
        try {
            $this->assertAttributeNotContains('baz', 'privateArray', $obj);
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }
        $this->fail();
    }
    public function testAssertAttributeContainsNonObject()
    {
        $obj = new ClassWithNonPublicAttributes;
        $this->assertAttributeContains(true, 'privateArray', $obj);
        try {
            $this->assertAttributeContains(true, 'privateArray', $obj, '', false, true, true);
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }
        $this->fail();
    }
    public function testAssertAttributeNotContainsNonObject()
    {
        $obj = new ClassWithNonPublicAttributes;
        $this->assertAttributeNotContains(true, 'privateArray', $obj, '', false, true, true);
        try {
            $this->assertAttributeNotContains(true, 'privateArray', $obj);
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }
        $this->fail();
    }
    public function testAssertPublicAttributeEquals()
    {
        $obj = new ClassWithNonPublicAttributes;
        $this->assertAttributeEquals('foo', 'publicAttribute', $obj);
        try {
            $this->assertAttributeEquals('bar', 'publicAttribute', $obj);
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }
        $this->fail();
    }
    public function testAssertPublicAttributeNotEquals()
    {
        $obj = new ClassWithNonPublicAttributes;
        $this->assertAttributeNotEquals('bar', 'publicAttribute', $obj);
        try {
            $this->assertAttributeNotEquals('foo', 'publicAttribute', $obj);
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }
        $this->fail();
    }
    public function testAssertPublicAttributeSame()
    {
        $obj = new ClassWithNonPublicAttributes;
        $this->assertAttributeSame('foo', 'publicAttribute', $obj);
        try {
            $this->assertAttributeSame('bar', 'publicAttribute', $obj);
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }
        $this->fail();
    }
    public function testAssertPublicAttributeNotSame()
    {
        $obj = new ClassWithNonPublicAttributes;
        $this->assertAttributeNotSame('bar', 'publicAttribute', $obj);
        try {
            $this->assertAttributeNotSame('foo', 'publicAttribute', $obj);
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }
        $this->fail();
    }
    public function testAssertProtectedAttributeEquals()
    {
        $obj = new ClassWithNonPublicAttributes;
        $this->assertAttributeEquals('bar', 'protectedAttribute', $obj);
        try {
            $this->assertAttributeEquals('foo', 'protectedAttribute', $obj);
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }
        $this->fail();
    }
    public function testAssertProtectedAttributeNotEquals()
    {
        $obj = new ClassWithNonPublicAttributes;
        $this->assertAttributeNotEquals('foo', 'protectedAttribute', $obj);
        try {
            $this->assertAttributeNotEquals('bar', 'protectedAttribute', $obj);
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }
        $this->fail();
    }
    public function testAssertPrivateAttributeEquals()
    {
        $obj = new ClassWithNonPublicAttributes;
        $this->assertAttributeEquals('baz', 'privateAttribute', $obj);
        try {
            $this->assertAttributeEquals('foo', 'privateAttribute', $obj);
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }
        $this->fail();
    }
    public function testAssertPrivateAttributeNotEquals()
    {
        $obj = new ClassWithNonPublicAttributes;
        $this->assertAttributeNotEquals('foo', 'privateAttribute', $obj);
        try {
            $this->assertAttributeNotEquals('baz', 'privateAttribute', $obj);
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }
        $this->fail();
    }
    public function testAssertPublicStaticAttributeEquals()
    {
        $this->assertAttributeEquals('foo', 'publicStaticAttribute', 'ClassWithNonPublicAttributes');
        try {
            $this->assertAttributeEquals('bar', 'publicStaticAttribute', 'ClassWithNonPublicAttributes');
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }
        $this->fail();
    }
    public function testAssertPublicStaticAttributeNotEquals()
    {
        $this->assertAttributeNotEquals('bar', 'publicStaticAttribute', 'ClassWithNonPublicAttributes');
        try {
            $this->assertAttributeNotEquals('foo', 'publicStaticAttribute', 'ClassWithNonPublicAttributes');
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }
        $this->fail();
    }
    public function testAssertProtectedStaticAttributeEquals()
    {
        $this->assertAttributeEquals('bar', 'protectedStaticAttribute', 'ClassWithNonPublicAttributes');
        try {
            $this->assertAttributeEquals('foo', 'protectedStaticAttribute', 'ClassWithNonPublicAttributes');
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }
        $this->fail();
    }
    public function testAssertProtectedStaticAttributeNotEquals()
    {
        $this->assertAttributeNotEquals('foo', 'protectedStaticAttribute', 'ClassWithNonPublicAttributes');
        try {
            $this->assertAttributeNotEquals('bar', 'protectedStaticAttribute', 'ClassWithNonPublicAttributes');
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }
        $this->fail();
    }
    public function testAssertPrivateStaticAttributeEquals()
    {
        $this->assertAttributeEquals('baz', 'privateStaticAttribute', 'ClassWithNonPublicAttributes');
        try {
            $this->assertAttributeEquals('foo', 'privateStaticAttribute', 'ClassWithNonPublicAttributes');
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }
        $this->fail();
    }
    public function testAssertPrivateStaticAttributeNotEquals()
    {
        $this->assertAttributeNotEquals('foo', 'privateStaticAttribute', 'ClassWithNonPublicAttributes');
        try {
            $this->assertAttributeNotEquals('baz', 'privateStaticAttribute', 'ClassWithNonPublicAttributes');
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }
        $this->fail();
    }
    public function testAssertClassHasAttributeThrowsException()
    {
        $this->assertClassHasAttribute(null, null);
    }
    public function testAssertClassHasAttributeThrowsException2()
    {
        $this->assertClassHasAttribute('foo', null);
    }
    public function testAssertClassHasAttributeThrowsExceptionIfAttributeNameIsNotValid()
    {
        $this->assertClassHasAttribute('1', 'ClassWithNonPublicAttributes');
    }
    public function testAssertClassNotHasAttributeThrowsException()
    {
        $this->assertClassNotHasAttribute(null, null);
    }
    public function testAssertClassNotHasAttributeThrowsException2()
    {
        $this->assertClassNotHasAttribute('foo', null);
    }
    public function testAssertClassNotHasAttributeThrowsExceptionIfAttributeNameIsNotValid()
    {
        $this->assertClassNotHasAttribute('1', 'ClassWithNonPublicAttributes');
    }
    public function testAssertClassHasStaticAttributeThrowsException()
    {
        $this->assertClassHasStaticAttribute(null, null);
    }
    public function testAssertClassHasStaticAttributeThrowsException2()
    {
        $this->assertClassHasStaticAttribute('foo', null);
    }
    public function testAssertClassHasStaticAttributeThrowsExceptionIfAttributeNameIsNotValid()
    {
        $this->assertClassHasStaticAttribute('1', 'ClassWithNonPublicAttributes');
    }
    public function testAssertClassNotHasStaticAttributeThrowsException()
    {
        $this->assertClassNotHasStaticAttribute(null, null);
    }
    public function testAssertClassNotHasStaticAttributeThrowsException2()
    {
        $this->assertClassNotHasStaticAttribute('foo', null);
    }
    public function testAssertClassNotHasStaticAttributeThrowsExceptionIfAttributeNameIsNotValid()
    {
        $this->assertClassNotHasStaticAttribute('1', 'ClassWithNonPublicAttributes');
    }
    public function testAssertObjectHasAttributeThrowsException()
    {
        $this->assertObjectHasAttribute(null, null);
    }
    public function testAssertObjectHasAttributeThrowsException2()
    {
        $this->assertObjectHasAttribute('foo', null);
    }
    public function testAssertObjectHasAttributeThrowsExceptionIfAttributeNameIsNotValid()
    {
        $this->assertObjectHasAttribute('1', 'ClassWithNonPublicAttributes');
    }
    public function testAssertObjectNotHasAttributeThrowsException()
    {
        $this->assertObjectNotHasAttribute(null, null);
    }
    public function testAssertObjectNotHasAttributeThrowsException2()
    {
        $this->assertObjectNotHasAttribute('foo', null);
    }
    public function testAssertObjectNotHasAttributeThrowsExceptionIfAttributeNameIsNotValid()
    {
        $this->assertObjectNotHasAttribute('1', 'ClassWithNonPublicAttributes');
    }
    public function testClassHasPublicAttribute()
    {
        $this->assertClassHasAttribute('publicAttribute', 'ClassWithNonPublicAttributes');
        try {
            $this->assertClassHasAttribute('attribute', 'ClassWithNonPublicAttributes');
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }
        $this->fail();
    }
    public function testClassNotHasPublicAttribute()
    {
        $this->assertClassNotHasAttribute('attribute', 'ClassWithNonPublicAttributes');
        try {
            $this->assertClassNotHasAttribute('publicAttribute', 'ClassWithNonPublicAttributes');
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }
        $this->fail();
    }
    public function testClassHasPublicStaticAttribute()
    {
        $this->assertClassHasStaticAttribute('publicStaticAttribute', 'ClassWithNonPublicAttributes');
        try {
            $this->assertClassHasStaticAttribute('attribute', 'ClassWithNonPublicAttributes');
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }
        $this->fail();
    }
    public function testClassNotHasPublicStaticAttribute()
    {
        $this->assertClassNotHasStaticAttribute('attribute', 'ClassWithNonPublicAttributes');
        try {
            $this->assertClassNotHasStaticAttribute('publicStaticAttribute', 'ClassWithNonPublicAttributes');
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }
        $this->fail();
    }
    public function testObjectHasPublicAttribute()
    {
        $obj = new ClassWithNonPublicAttributes;
        $this->assertObjectHasAttribute('publicAttribute', $obj);
        try {
            $this->assertObjectHasAttribute('attribute', $obj);
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }
        $this->fail();
    }
    public function testObjectNotHasPublicAttribute()
    {
        $obj = new ClassWithNonPublicAttributes;
        $this->assertObjectNotHasAttribute('attribute', $obj);
        try {
            $this->assertObjectNotHasAttribute('publicAttribute', $obj);
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }
        $this->fail();
    }
    public function testObjectHasOnTheFlyAttribute()
    {
        $obj = new StdClass;
        $obj->foo = 'bar';
        $this->assertObjectHasAttribute('foo', $obj);
        try {
            $this->assertObjectHasAttribute('bar', $obj);
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }
        $this->fail();
    }
    public function testObjectNotHasOnTheFlyAttribute()
    {
        $obj = new StdClass;
        $obj->foo = 'bar';
        $this->assertObjectNotHasAttribute('bar', $obj);
        try {
            $this->assertObjectNotHasAttribute('foo', $obj);
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }
        $this->fail();
    }
    public function testObjectHasProtectedAttribute()
    {
        $obj = new ClassWithNonPublicAttributes;
        $this->assertObjectHasAttribute('protectedAttribute', $obj);
        try {
            $this->assertObjectHasAttribute('attribute', $obj);
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }
        $this->fail();
    }
    public function testObjectNotHasProtectedAttribute()
    {
        $obj = new ClassWithNonPublicAttributes;
        $this->assertObjectNotHasAttribute('attribute', $obj);
        try {
            $this->assertObjectNotHasAttribute('protectedAttribute', $obj);
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }
        $this->fail();
    }
    public function testObjectHasPrivateAttribute()
    {
        $obj = new ClassWithNonPublicAttributes;
        $this->assertObjectHasAttribute('privateAttribute', $obj);
        try {
            $this->assertObjectHasAttribute('attribute', $obj);
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }
        $this->fail();
    }
    public function testObjectNotHasPrivateAttribute()
    {
        $obj = new ClassWithNonPublicAttributes;
        $this->assertObjectNotHasAttribute('attribute', $obj);
        try {
            $this->assertObjectNotHasAttribute('privateAttribute', $obj);
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }
        $this->fail();
    }
    public function testAssertThatAttributeEquals()
    {
        $this->assertThat(
            new ClassWithNonPublicAttributes,
            $this->attribute(
                $this->equalTo('foo'),
                'publicAttribute'
            )
        );
    }
    public function testAssertThatAttributeEquals2()
    {
        $this->assertThat(
            new ClassWithNonPublicAttributes,
            $this->attribute(
                $this->equalTo('bar'),
                'publicAttribute'
            )
        );
    }
    public function testAssertThatAttributeEqualTo()
    {
        $this->assertThat(
            new ClassWithNonPublicAttributes,
            $this->attributeEqualTo('publicAttribute', 'foo')
        );
    }
    public function testAssertThatAnything()
    {
        $this->assertThat('anything', $this->anything());
    }
    public function testAssertThatAnythingAndAnything()
    {
        $this->assertThat(
            'anything',
            $this->logicalAnd(
                $this->anything(), $this->anything()
            )
        );
    }
    public function testAssertThatAnythingOrAnything()
    {
        $this->assertThat(
            'anything',
            $this->logicalOr(
                $this->anything(), $this->anything()
            )
        );
    }
    public function testAssertThatAnythingXorNotAnything()
    {
        $this->assertThat(
            'anything',
            $this->logicalXor(
                $this->anything(),
                $this->logicalNot($this->anything())
            )
        );
    }
    public function testAssertThatContains()
    {
        $this->assertThat(array('foo'), $this->contains('foo'));
    }
    public function testAssertThatStringContains()
    {
        $this->assertThat('barfoobar', $this->stringContains('foo'));
    }
    public function testAssertThatContainsOnly()
    {
        $this->assertThat(array('foo'), $this->containsOnly('string'));
    }
    public function testAssertThatContainsOnlyInstancesOf()
    {
        $this->assertThat(array(new Book), $this->containsOnlyInstancesOf('Book'));
    }
    public function testAssertThatArrayHasKey()
    {
        $this->assertThat(array('foo' => 'bar'), $this->arrayHasKey('foo'));
    }
    public function testAssertThatClassHasAttribute()
    {
        $this->assertThat(
            new ClassWithNonPublicAttributes,
            $this->classHasAttribute('publicAttribute')
        );
    }
    public function testAssertThatClassHasStaticAttribute()
    {
        $this->assertThat(
            new ClassWithNonPublicAttributes,
            $this->classHasStaticAttribute('publicStaticAttribute')
        );
    }
    public function testAssertThatObjectHasAttribute()
    {
        $this->assertThat(
            new ClassWithNonPublicAttributes,
            $this->objectHasAttribute('publicAttribute')
        );
    }
    public function testAssertThatEqualTo()
    {
        $this->assertThat('foo', $this->equalTo('foo'));
    }
    public function testAssertThatIdenticalTo()
    {
        $value      = new StdClass;
        $constraint = $this->identicalTo($value);
        $this->assertThat($value, $constraint);
    }
    public function testAssertThatIsInstanceOf()
    {
        $this->assertThat(new StdClass, $this->isInstanceOf('StdClass'));
    }
    public function testAssertThatIsType()
    {
        $this->assertThat('string', $this->isType('string'));
    }
    public function testAssertThatIsEmpty()
    {
        $this->assertThat(array(), $this->isEmpty());
    }
    public function testAssertThatFileExists()
    {
        $this->assertThat(__FILE__, $this->fileExists());
    }
    public function testAssertThatGreaterThan()
    {
        $this->assertThat(2, $this->greaterThan(1));
    }
    public function testAssertThatGreaterThanOrEqual()
    {
        $this->assertThat(2, $this->greaterThanOrEqual(1));
    }
    public function testAssertThatLessThan()
    {
        $this->assertThat(1, $this->lessThan(2));
    }
    public function testAssertThatLessThanOrEqual()
    {
        $this->assertThat(1, $this->lessThanOrEqual(2));
    }
    public function testAssertThatMatchesRegularExpression()
    {
        $this->assertThat('foobar', $this->matchesRegularExpression('/foo/'));
    }
    public function testAssertThatCallback()
    {
        $this->assertThat(null, $this->callback(function ($other) { return true;
        }));
    }
    public function testAssertThatCountOf()
    {
        $this->assertThat(array(1), $this->countOf(1));
    }
    public function testAssertFileEquals()
    {
        $this->assertFileEquals(
            $this->filesDirectory . 'foo.xml',
            $this->filesDirectory . 'foo.xml'
        );
        try {
            $this->assertFileEquals(
                $this->filesDirectory . 'foo.xml',
                $this->filesDirectory . 'bar.xml'
            );
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }
        $this->fail();
    }
    public function testAssertFileNotEquals()
    {
        $this->assertFileNotEquals(
            $this->filesDirectory . 'foo.xml',
            $this->filesDirectory . 'bar.xml'
        );
        try {
            $this->assertFileNotEquals(
                $this->filesDirectory . 'foo.xml',
                $this->filesDirectory . 'foo.xml'
            );
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }
        $this->fail();
    }
    public function testAssertStringEqualsFile()
    {
        $this->assertStringEqualsFile(
            $this->filesDirectory . 'foo.xml',
            file_get_contents($this->filesDirectory . 'foo.xml')
        );
        try {
            $this->assertStringEqualsFile(
                $this->filesDirectory . 'foo.xml',
                file_get_contents($this->filesDirectory . 'bar.xml')
            );
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }
        $this->fail();
    }
    public function testAssertStringNotEqualsFile()
    {
        $this->assertStringNotEqualsFile(
            $this->filesDirectory . 'foo.xml',
            file_get_contents($this->filesDirectory . 'bar.xml')
        );
        try {
            $this->assertStringNotEqualsFile(
                $this->filesDirectory . 'foo.xml',
                file_get_contents($this->filesDirectory . 'foo.xml')
            );
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }
        $this->fail();
    }
    public function testAssertStringStartsWithThrowsException()
    {
        $this->assertStringStartsWith(null, null);
    }
    public function testAssertStringStartsWithThrowsException2()
    {
        $this->assertStringStartsWith('', null);
    }
    public function testAssertStringStartsNotWithThrowsException()
    {
        $this->assertStringStartsNotWith(null, null);
    }
    public function testAssertStringStartsNotWithThrowsException2()
    {
        $this->assertStringStartsNotWith('', null);
    }
    public function testAssertStringEndsWithThrowsException()
    {
        $this->assertStringEndsWith(null, null);
    }
    public function testAssertStringEndsWithThrowsException2()
    {
        $this->assertStringEndsWith('', null);
    }
    public function testAssertStringEndsNotWithThrowsException()
    {
        $this->assertStringEndsNotWith(null, null);
    }
    public function testAssertStringEndsNotWithThrowsException2()
    {
        $this->assertStringEndsNotWith('', null);
    }
    public function testAssertStringStartsWith()
    {
        $this->assertStringStartsWith('prefix', 'prefixfoo');
        try {
            $this->assertStringStartsWith('prefix', 'foo');
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }
        $this->fail();
    }
    public function testAssertStringStartsNotWith()
    {
        $this->assertStringStartsNotWith('prefix', 'foo');
        try {
            $this->assertStringStartsNotWith('prefix', 'prefixfoo');
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }
        $this->fail();
    }
    public function testAssertStringEndsWith()
    {
        $this->assertStringEndsWith('suffix', 'foosuffix');
        try {
            $this->assertStringEndsWith('suffix', 'foo');
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }
        $this->fail();
    }
    public function testAssertStringEndsNotWith()
    {
        $this->assertStringEndsNotWith('suffix', 'foo');
        try {
            $this->assertStringEndsNotWith('suffix', 'foosuffix');
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }
        $this->fail();
    }
    public function testAssertStringMatchesFormatRaisesExceptionForInvalidFirstArgument()
    {
        $this->assertStringMatchesFormat(null, '');
    }
    public function testAssertStringMatchesFormatRaisesExceptionForInvalidSecondArgument()
    {
        $this->assertStringMatchesFormat('', null);
    }
    public function testAssertStringMatchesFormat()
    {
        $this->assertStringMatchesFormat('*%s*', '***');
    }
    public function testAssertStringMatchesFormatFailure()
    {
        $this->assertStringMatchesFormat('*%s*', '**');
    }
    public function testAssertStringNotMatchesFormatRaisesExceptionForInvalidFirstArgument()
    {
        $this->assertStringNotMatchesFormat(null, '');
    }
    public function testAssertStringNotMatchesFormatRaisesExceptionForInvalidSecondArgument()
    {
        $this->assertStringNotMatchesFormat('', null);
    }
    public function testAssertStringNotMatchesFormat()
    {
        $this->assertStringNotMatchesFormat('*%s*', '**');
        try {
            $this->assertStringMatchesFormat('*%s*', '**');
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }
        $this->fail();
    }
    public function testAssertEmpty()
    {
        $this->assertEmpty(array());
        try {
            $this->assertEmpty(array('foo'));
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }
        $this->fail();
    }
    public function testAssertNotEmpty()
    {
        $this->assertNotEmpty(array('foo'));
        try {
            $this->assertNotEmpty(array());
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }
        $this->fail();
    }
    public function testAssertAttributeEmpty()
    {
        $o    = new StdClass;
        $o->a = array();
        $this->assertAttributeEmpty('a', $o);
        try {
            $o->a = array('b');
            $this->assertAttributeEmpty('a', $o);
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }
        $this->fail();
    }
    public function testAssertAttributeNotEmpty()
    {
        $o    = new StdClass;
        $o->a = array('b');
        $this->assertAttributeNotEmpty('a', $o);
        try {
            $o->a = array();
            $this->assertAttributeNotEmpty('a', $o);
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }
        $this->fail();
    }
    public function testMarkTestIncomplete()
    {
        try {
            $this->markTestIncomplete('incomplete');
        } catch (PHPUnit_Framework_IncompleteTestError $e) {
            $this->assertEquals('incomplete', $e->getMessage());
            return;
        }
        $this->fail();
    }
    public function testMarkTestSkipped()
    {
        try {
            $this->markTestSkipped('skipped');
        } catch (PHPUnit_Framework_SkippedTestError $e) {
            $this->assertEquals('skipped', $e->getMessage());
            return;
        }
        $this->fail();
    }
    public function testAssertCount()
    {
        $this->assertCount(2, array(1, 2));
        try {
            $this->assertCount(2, array(1, 2, 3));
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }
        $this->fail();
    }
    public function testAssertCountTraversable()
    {
        $this->assertCount(2, new ArrayIterator(array(1, 2)));
        try {
            $this->assertCount(2, new ArrayIterator(array(1, 2, 3)));
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }
        $this->fail();
    }
    public function testAssertCountThrowsExceptionIfExpectedCountIsNoInteger()
    {
        try {
            $this->assertCount('a', array());
        } catch (PHPUnit_Framework_Exception $e) {
            $this->assertEquals('Argument #1 (No Value) of PHPUnit_Framework_Assert::assertCount() must be a integer', $e->getMessage());
            return;
        }
        $this->fail();
    }
    public function testAssertCountThrowsExceptionIfElementIsNotCountable()
    {
        try {
            $this->assertCount(2, '');
        } catch (PHPUnit_Framework_Exception $e) {
            $this->assertEquals('Argument #2 (No Value) of PHPUnit_Framework_Assert::assertCount() must be a countable or traversable', $e->getMessage());
            return;
        }
        $this->fail();
    }
    public function testAssertAttributeCount()
    {
        $o    = new stdClass;
        $o->a = array();
        $this->assertAttributeCount(0, 'a', $o);
    }
    public function testAssertNotCount()
    {
        $this->assertNotCount(2, array(1, 2, 3));
        try {
            $this->assertNotCount(2, array(1, 2));
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }
        $this->fail();
    }
    public function testAssertNotCountThrowsExceptionIfExpectedCountIsNoInteger()
    {
        $this->assertNotCount('a', array());
    }
    public function testAssertNotCountThrowsExceptionIfElementIsNotCountable()
    {
        $this->assertNotCount(2, '');
    }
    public function testAssertAttributeNotCount()
    {
        $o    = new stdClass;
        $o->a = array();
        $this->assertAttributeNotCount(1, 'a', $o);
    }
    public function testAssertSameSize()
    {
        $this->assertSameSize(array(1, 2), array(3, 4));
        try {
            $this->assertSameSize(array(1, 2), array(1, 2, 3));
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }
        $this->fail();
    }
    public function testAssertSameSizeThrowsExceptionIfExpectedIsNotCountable()
    {
        try {
            $this->assertSameSize('a', array());
        } catch (PHPUnit_Framework_Exception $e) {
            $this->assertEquals('Argument #1 (No Value) of PHPUnit_Framework_Assert::assertSameSize() must be a countable or traversable', $e->getMessage());
            return;
        }
        $this->fail();
    }
    public function testAssertSameSizeThrowsExceptionIfActualIsNotCountable()
    {
        try {
            $this->assertSameSize(array(), '');
        } catch (PHPUnit_Framework_Exception $e) {
            $this->assertEquals('Argument #2 (No Value) of PHPUnit_Framework_Assert::assertSameSize() must be a countable or traversable', $e->getMessage());
            return;
        }
        $this->fail();
    }
    public function testAssertNotSameSize()
    {
        $this->assertNotSameSize(array(1, 2), array(1, 2, 3));
        try {
            $this->assertNotSameSize(array(1, 2), array(3, 4));
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }
        $this->fail();
    }
    public function testAssertNotSameSizeThrowsExceptionIfExpectedIsNotCountable()
    {
        $this->assertNotSameSize('a', array());
    }
    public function testAssertNotSameSizeThrowsExceptionIfActualIsNotCountable()
    {
        $this->assertNotSameSize(array(), '');
    }
    public function testAssertJsonRaisesExceptionForInvalidArgument()
    {
        $this->assertJson(null);
    }
    public function testAssertJsonStringEqualsJsonString()
    {
        $expected = '{"Mascott" : "Tux"}';
        $actual   = '{"Mascott" : "Tux"}';
        $message  = 'Given Json strings do not match';
        $this->assertJsonStringEqualsJsonString($expected, $actual, $message);
    }
    public function testAssertJsonStringEqualsJsonStringErrorRaised($expected, $actual)
    {
        try {
            $this->assertJsonStringEqualsJsonString($expected, $actual);
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }
        $this->fail('Expected exception not found');
    }
    public function testAssertJsonStringNotEqualsJsonString()
    {
        $expected = '{"Mascott" : "Beastie"}';
        $actual   = '{"Mascott" : "Tux"}';
        $message  = 'Given Json strings do match';
        $this->assertJsonStringNotEqualsJsonString($expected, $actual, $message);
    }
    public function testAssertJsonStringNotEqualsJsonStringErrorRaised($expected, $actual)
    {
        try {
            $this->assertJsonStringNotEqualsJsonString($expected, $actual);
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }
        $this->fail('Expected exception not found');
    }
    public function testAssertJsonStringEqualsJsonFile()
    {
        $file = __DIR__ . '/../_files/JsonData/simpleObject.json';
        $actual = json_encode(array("Mascott" => "Tux"));
        $message = '';
        $this->assertJsonStringEqualsJsonFile($file, $actual, $message);
    }
    public function testAssertJsonStringEqualsJsonFileExpectingExpectationFailedException()
    {
        $file = __DIR__ . '/../_files/JsonData/simpleObject.json';
        $actual = json_encode(array("Mascott" => "Beastie"));
        $message = '';
        try {
            $this->assertJsonStringEqualsJsonFile($file, $actual, $message);
        } catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
                'Failed asserting that \'{"Mascott":"Beastie"}\' matches JSON string "{"Mascott":"Tux"}".',
                $e->getMessage()
            );
            return;
        }
        $this->fail('Expected Exception not thrown.');
    }
    public function testAssertJsonStringEqualsJsonFileExpectingException()
    {
        $file = __DIR__ . '/../_files/JsonData/simpleObject.json';
        try {
            $this->assertJsonStringEqualsJsonFile($file, null);
        } catch (PHPUnit_Framework_Exception $e) {
            return;
        }
        $this->fail('Expected Exception not thrown.');
    }
    public function testAssertJsonStringNotEqualsJsonFile()
    {
        $file = __DIR__ . '/../_files/JsonData/simpleObject.json';
        $actual = json_encode(array("Mascott" => "Beastie"));
        $message = '';
        $this->assertJsonStringNotEqualsJsonFile($file, $actual, $message);
    }
    public function testAssertJsonStringNotEqualsJsonFileExpectingException()
    {
        $file = __DIR__ . '/../_files/JsonData/simpleObject.json';
        try {
            $this->assertJsonStringNotEqualsJsonFile($file, null);
        } catch (PHPUnit_Framework_Exception $e) {
            return;
        }
        $this->fail('Expected exception not found.');
    }
    public function testAssertJsonFileNotEqualsJsonFile()
    {
        $fileExpected = __DIR__ . '/../_files/JsonData/simpleObject.json';
        $fileActual   = __DIR__ . '/../_files/JsonData/arrayObject.json';
        $message = '';
        $this->assertJsonFileNotEqualsJsonFile($fileExpected, $fileActual, $message);
    }
    public function testAssertJsonFileEqualsJsonFile()
    {
        $file = __DIR__ . '/../_files/JsonData/simpleObject.json';
        $message = '';
        $this->assertJsonFileEqualsJsonFile($file, $file, $message);
    }
    public function testAssertInstanceOf()
    {
        $this->assertInstanceOf('stdClass', new stdClass);
        try {
            $this->assertInstanceOf('Exception', new stdClass);
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }
        $this->fail();
    }
    public function testAssertInstanceOfThrowsExceptionForInvalidArgument()
    {
        $this->assertInstanceOf(null, new stdClass);
    }
    public function testAssertAttributeInstanceOf()
    {
        $o    = new stdClass;
        $o->a = new stdClass;
        $this->assertAttributeInstanceOf('stdClass', 'a', $o);
    }
    public function testAssertNotInstanceOf()
    {
        $this->assertNotInstanceOf('Exception', new stdClass);
        try {
            $this->assertNotInstanceOf('stdClass', new stdClass);
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }
        $this->fail();
    }
    public function testAssertNotInstanceOfThrowsExceptionForInvalidArgument()
    {
        $this->assertNotInstanceOf(null, new stdClass);
    }
    public function testAssertAttributeNotInstanceOf()
    {
        $o    = new stdClass;
        $o->a = new stdClass;
        $this->assertAttributeNotInstanceOf('Exception', 'a', $o);
    }
    public function testAssertInternalType()
    {
        $this->assertInternalType('integer', 1);
        try {
            $this->assertInternalType('string', 1);
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }
        $this->fail();
    }
    public function testAssertInternalTypeDouble()
    {
        $this->assertInternalType('double', 1.0);
        try {
            $this->assertInternalType('double', 1);
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }
        $this->fail();
    }
    public function testAssertInternalTypeThrowsExceptionForInvalidArgument()
    {
        $this->assertInternalType(null, 1);
    }
    public function testAssertAttributeInternalType()
    {
        $o    = new stdClass;
        $o->a = 1;
        $this->assertAttributeInternalType('integer', 'a', $o);
    }
    public function testAssertNotInternalType()
    {
        $this->assertNotInternalType('string', 1);
        try {
            $this->assertNotInternalType('integer', 1);
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }
        $this->fail();
    }
    public function testAssertNotInternalTypeThrowsExceptionForInvalidArgument()
    {
        $this->assertNotInternalType(null, 1);
    }
    public function testAssertAttributeNotInternalType()
    {
        $o    = new stdClass;
        $o->a = 1;
        $this->assertAttributeNotInternalType('string', 'a', $o);
    }
    public static function validInvalidJsonDataprovider()
    {
        return array(
            'error syntax in expected JSON' => array('{"Mascott"::}', '{"Mascott" : "Tux"}'),
            'error UTF-8 in actual JSON'    => array('{"Mascott" : "Tux"}', '{"Mascott" : :}'),
        );
    }
}
