<?php
class Swift_Mime_Headers_IdentificationHeaderTest extends \PHPUnit_Framework_TestCase
{
    public function testTypeIsIdHeader()
    {
        $header = $this->_getHeader('Message-ID');
        $this->assertEquals(Swift_Mime_Header::TYPE_ID, $header->getFieldType());
    }
    public function testValueMatchesMsgIdSpec()
    {
        $header = $this->_getHeader('Message-ID');
        $header->setId('id-left@id-right');
        $this->assertEquals('<id-left@id-right>', $header->getFieldBody());
    }
    public function testIdCanBeRetrievedVerbatim()
    {
        $header = $this->_getHeader('Message-ID');
        $header->setId('id-left@id-right');
        $this->assertEquals('id-left@id-right', $header->getId());
    }
    public function testMultipleIdsCanBeSet()
    {
        $header = $this->_getHeader('References');
        $header->setIds(array('a@b', 'x@y'));
        $this->assertEquals(array('a@b', 'x@y'), $header->getIds());
    }
    public function testSettingMultipleIdsProducesAListValue()
    {
        $header = $this->_getHeader('References');
        $header->setIds(array('a@b', 'x@y'));
        $this->assertEquals('<a@b> <x@y>', $header->getFieldBody());
    }
    public function testIdLeftCanBeQuoted()
    {
        $header = $this->_getHeader('References');
        $header->setId('"ab"@c');
        $this->assertEquals('"ab"@c', $header->getId());
        $this->assertEquals('<"ab"@c>', $header->getFieldBody());
    }
    public function testIdLeftCanContainAnglesAsQuotedPairs()
    {
        $header = $this->_getHeader('References');
        $header->setId('"a\\<\\>b"@c');
        $this->assertEquals('"a\\<\\>b"@c', $header->getId());
        $this->assertEquals('<"a\\<\\>b"@c>', $header->getFieldBody());
    }
    public function testIdLeftCanBeDotAtom()
    {
        $header = $this->_getHeader('References');
        $header->setId('a.b+&%$.c@d');
        $this->assertEquals('a.b+&%$.c@d', $header->getId());
        $this->assertEquals('<a.b+&%$.c@d>', $header->getFieldBody());
    }
    public function testInvalidIdLeftThrowsException()
    {
        try {
            $header = $this->_getHeader('References');
            $header->setId('a b c@d');
            $this->fail(
                'Exception should be thrown since "a b c" is not valid id-left.'
                );
        } catch (Exception $e) {
        }
    }
    public function testIdRightCanBeDotAtom()
    {
        $header = $this->_getHeader('References');
        $header->setId('a@b.c+&%$.d');
        $this->assertEquals('a@b.c+&%$.d', $header->getId());
        $this->assertEquals('<a@b.c+&%$.d>', $header->getFieldBody());
    }
    public function testIdRightCanBeLiteral()
    {
        $header = $this->_getHeader('References');
        $header->setId('a@[1.2.3.4]');
        $this->assertEquals('a@[1.2.3.4]', $header->getId());
        $this->assertEquals('<a@[1.2.3.4]>', $header->getFieldBody());
    }
    public function testInvalidIdRightThrowsException()
    {
        try {
            $header = $this->_getHeader('References');
            $header->setId('a@b c d');
            $this->fail(
                'Exception should be thrown since "b c d" is not valid id-right.'
                );
        } catch (Exception $e) {
        }
    }
    public function testMissingAtSignThrowsException()
    {
        try {
            $header = $this->_getHeader('References');
            $header->setId('abc');
            $this->fail(
                'Exception should be thrown since "abc" is does not contain @.'
                );
        } catch (Exception $e) {
        }
    }
    public function testSetBodyModel()
    {
        $header = $this->_getHeader('Message-ID');
        $header->setFieldBodyModel('a@b');
        $this->assertEquals(array('a@b'), $header->getIds());
    }
    public function testGetBodyModel()
    {
        $header = $this->_getHeader('Message-ID');
        $header->setId('a@b');
        $this->assertEquals(array('a@b'), $header->getFieldBodyModel());
    }
    public function testStringValue()
    {
        $header = $this->_getHeader('References');
        $header->setIds(array('a@b', 'x@y'));
        $this->assertEquals('References: <a@b> <x@y>'."\r\n", $header->toString());
    }
    private function _getHeader($name)
    {
        return new Swift_Mime_Headers_IdentificationHeader($name, new Swift_Mime_Grammar());
    }
}
