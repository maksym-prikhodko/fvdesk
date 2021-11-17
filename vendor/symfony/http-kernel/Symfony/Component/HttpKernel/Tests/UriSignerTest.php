<?php
namespace Symfony\Component\HttpKernel\Tests;
use Symfony\Component\HttpKernel\UriSigner;
class UriSignerTest extends \PHPUnit_Framework_TestCase
{
    public function testSign()
    {
        $signer = new UriSigner('foobar');
        $this->assertContains('?_hash=', $signer->sign('http:
        $this->assertContains('&_hash=', $signer->sign('http:
    }
    public function testCheck()
    {
        $signer = new UriSigner('foobar');
        $this->assertFalse($signer->check('http:
        $this->assertFalse($signer->check('http:
        $this->assertFalse($signer->check('http:
        $this->assertTrue($signer->check($signer->sign('http:
        $this->assertTrue($signer->check($signer->sign('http:
        $this->assertTrue($signer->sign('http:
    }
    public function testCheckWithDifferentArgSeparator()
    {
        $this->iniSet('arg_separator.output', '&amp;');
        $signer = new UriSigner('foobar');
        $this->assertSame(
            "http:
            $signer->sign('http:
        );
        $this->assertTrue($signer->check($signer->sign('http:
    }
}
