<?php
class Swift_Mime_MimePartTest extends Swift_Mime_AbstractMimeEntityTest
{
    public function testNestingLevelIsSubpart()
    {
        $part = $this->_createMimePart($this->_createHeaderSet(),
            $this->_createEncoder(), $this->_createCache()
            );
        $this->assertEquals(
            Swift_Mime_MimeEntity::LEVEL_ALTERNATIVE, $part->getNestingLevel()
            );
    }
    public function testCharsetIsReturnedFromHeader()
    {
        $cType = $this->_createHeader('Content-Type', 'text/plain',
            array('charset' => 'iso-8859-1')
            );
        $part = $this->_createMimePart($this->_createHeaderSet(array(
            'Content-Type' => $cType,)),
            $this->_createEncoder(), $this->_createCache()
            );
        $this->assertEquals('iso-8859-1', $part->getCharset());
    }
    public function testCharsetIsSetInHeader()
    {
        $cType = $this->_createHeader('Content-Type', 'text/plain',
            array('charset' => 'iso-8859-1'), false
            );
        $cType->shouldReceive('setParameter')->once()->with('charset', 'utf-8');
        $part = $this->_createMimePart($this->_createHeaderSet(array(
            'Content-Type' => $cType,)),
            $this->_createEncoder(), $this->_createCache()
            );
        $part->setCharset('utf-8');
    }
    public function testCharsetIsSetInHeaderIfPassedToSetBody()
    {
        $cType = $this->_createHeader('Content-Type', 'text/plain',
            array('charset' => 'iso-8859-1'), false
            );
        $cType->shouldReceive('setParameter')->once()->with('charset', 'utf-8');
        $part = $this->_createMimePart($this->_createHeaderSet(array(
            'Content-Type' => $cType,)),
            $this->_createEncoder(), $this->_createCache()
            );
        $part->setBody('', 'text/plian', 'utf-8');
    }
    public function testSettingCharsetNotifiesEncoder()
    {
        $encoder = $this->_createEncoder('quoted-printable', false);
        $encoder->expects($this->once())
                ->method('charsetChanged')
                ->with('utf-8');
        $part = $this->_createMimePart($this->_createHeaderSet(),
            $encoder, $this->_createCache()
            );
        $part->setCharset('utf-8');
    }
    public function testSettingCharsetNotifiesHeaders()
    {
        $headers = $this->_createHeaderSet(array(), false);
        $headers->shouldReceive('charsetChanged')
                ->zeroOrMoreTimes()
                ->with('utf-8');
        $part = $this->_createMimePart($headers, $this->_createEncoder(),
            $this->_createCache()
            );
        $part->setCharset('utf-8');
    }
    public function testSettingCharsetNotifiesChildren()
    {
        $child = $this->_createChild(0, '', false);
        $child->shouldReceive('charsetChanged')
              ->once()
              ->with('windows-874');
        $part = $this->_createMimePart($this->_createHeaderSet(),
            $this->_createEncoder(), $this->_createCache()
            );
        $part->setChildren(array($child));
        $part->setCharset('windows-874');
    }
    public function testCharsetChangeUpdatesCharset()
    {
        $cType = $this->_createHeader('Content-Type', 'text/plain',
            array('charset' => 'iso-8859-1'), false
            );
        $cType->shouldReceive('setParameter')->once()->with('charset', 'utf-8');
        $part = $this->_createMimePart($this->_createHeaderSet(array(
            'Content-Type' => $cType,)),
            $this->_createEncoder(), $this->_createCache()
            );
        $part->charsetChanged('utf-8');
    }
    public function testSettingCharsetClearsCache()
    {
        $headers = $this->_createHeaderSet(array(), false);
        $headers->shouldReceive('toString')
                ->zeroOrMoreTimes()
                ->andReturn("Content-Type: text/plain; charset=utf-8\r\n");
        $cache = $this->_createCache(false);
        $entity = $this->_createEntity($headers, $this->_createEncoder(),
            $cache
            );
        $entity->setBody("blah\r\nblah!");
        $entity->toString();
        $cache->shouldReceive('clearKey')
                ->once()
                ->with(\Mockery::any(), 'body');
        $entity->setCharset('iso-2022');
    }
    public function testFormatIsReturnedFromHeader()
    {
        $cType = $this->_createHeader('Content-Type', 'text/plain',
            array('format' => 'flowed')
            );
        $part = $this->_createMimePart($this->_createHeaderSet(array(
            'Content-Type' => $cType,)),
            $this->_createEncoder(), $this->_createCache()
            );
        $this->assertEquals('flowed', $part->getFormat());
    }
    public function testFormatIsSetInHeader()
    {
        $cType = $this->_createHeader('Content-Type', 'text/plain', array(), false);
        $cType->shouldReceive('setParameter')->once()->with('format', 'fixed');
        $part = $this->_createMimePart($this->_createHeaderSet(array(
            'Content-Type' => $cType,)),
            $this->_createEncoder(), $this->_createCache()
            );
        $part->setFormat('fixed');
    }
    public function testDelSpIsReturnedFromHeader()
    {
        $cType = $this->_createHeader('Content-Type', 'text/plain',
            array('delsp' => 'no')
            );
        $part = $this->_createMimePart($this->_createHeaderSet(array(
            'Content-Type' => $cType,)),
            $this->_createEncoder(), $this->_createCache()
            );
        $this->assertSame(false, $part->getDelSp());
    }
    public function testDelSpIsSetInHeader()
    {
        $cType = $this->_createHeader('Content-Type', 'text/plain', array(), false);
        $cType->shouldReceive('setParameter')->once()->with('delsp', 'yes');
        $part = $this->_createMimePart($this->_createHeaderSet(array(
            'Content-Type' => $cType,)),
            $this->_createEncoder(), $this->_createCache()
            );
        $part->setDelSp(true);
    }
    public function testFluidInterface()
    {
        $part = $this->_createMimePart($this->_createHeaderSet(),
            $this->_createEncoder(), $this->_createCache()
            );
        $this->assertSame($part,
            $part
            ->setContentType('text/plain')
            ->setEncoder($this->_createEncoder())
            ->setId('foo@bar')
            ->setDescription('my description')
            ->setMaxLineLength(998)
            ->setBody('xx')
            ->setBoundary('xyz')
            ->setChildren(array())
            ->setCharset('utf-8')
            ->setFormat('flowed')
            ->setDelSp(true)
            );
    }
    protected function _createEntity($headers, $encoder, $cache)
    {
        return $this->_createMimePart($headers, $encoder, $cache);
    }
    protected function _createMimePart($headers, $encoder, $cache)
    {
        return new Swift_Mime_MimePart($headers, $encoder, $cache, new Swift_Mime_Grammar());
    }
}
