<?php
namespace Symfony\Component\Security\Core\Tests\Encoder;
use Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder;
class MessageDigestPasswordEncoderTest extends \PHPUnit_Framework_TestCase
{
    public function testIsPasswordValid()
    {
        $encoder = new MessageDigestPasswordEncoder('sha256', false, 1);
        $this->assertTrue($encoder->isPasswordValid(hash('sha256', 'password'), 'password', ''));
    }
    public function testEncodePassword()
    {
        $encoder = new MessageDigestPasswordEncoder('sha256', false, 1);
        $this->assertSame(hash('sha256', 'password'), $encoder->encodePassword('password', ''));
        $encoder = new MessageDigestPasswordEncoder('sha256', true, 1);
        $this->assertSame(base64_encode(hash('sha256', 'password', true)), $encoder->encodePassword('password', ''));
        $encoder = new MessageDigestPasswordEncoder('sha256', false, 2);
        $this->assertSame(hash('sha256', hash('sha256', 'password', true).'password'), $encoder->encodePassword('password', ''));
    }
    public function testEncodePasswordAlgorithmDoesNotExist()
    {
        $encoder = new MessageDigestPasswordEncoder('foobar');
        $encoder->encodePassword('password', '');
    }
    public function testEncodePasswordLength()
    {
        $encoder = new MessageDigestPasswordEncoder();
        $encoder->encodePassword(str_repeat('a', 5000), 'salt');
    }
    public function testCheckPasswordLength()
    {
        $encoder = new MessageDigestPasswordEncoder();
        $this->assertFalse($encoder->isPasswordValid('encoded', str_repeat('a', 5000), 'salt'));
    }
}
