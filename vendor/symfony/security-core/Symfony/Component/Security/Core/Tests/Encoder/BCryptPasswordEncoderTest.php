<?php
namespace Symfony\Component\Security\Core\Tests\Encoder;
use Symfony\Component\Security\Core\Encoder\BCryptPasswordEncoder;
class BCryptPasswordEncoderTest extends \PHPUnit_Framework_TestCase
{
    const PASSWORD = 'password';
    const BYTES = '0123456789abcdef';
    const VALID_COST = '04';
    public function testCostBelowRange()
    {
        new BCryptPasswordEncoder(3);
    }
    public function testCostAboveRange()
    {
        new BCryptPasswordEncoder(32);
    }
    public function testCostInRange()
    {
        for ($cost = 4; $cost <= 31; $cost++) {
            new BCryptPasswordEncoder($cost);
        }
    }
    public function testResultLength()
    {
        $this->skipIfPhpVersionIsNotSupported();
        $encoder = new BCryptPasswordEncoder(self::VALID_COST);
        $result = $encoder->encodePassword(self::PASSWORD, null);
        $this->assertEquals(60, strlen($result));
    }
    public function testValidation()
    {
        $this->skipIfPhpVersionIsNotSupported();
        $encoder = new BCryptPasswordEncoder(self::VALID_COST);
        $result = $encoder->encodePassword(self::PASSWORD, null);
        $this->assertTrue($encoder->isPasswordValid($result, self::PASSWORD, null));
        $this->assertFalse($encoder->isPasswordValid($result, 'anotherPassword', null));
    }
    private function skipIfPhpVersionIsNotSupported()
    {
        if (PHP_VERSION_ID < 50307) {
            $this->markTestSkipped('Requires PHP >= 5.3.7');
        }
    }
    public function testEncodePasswordLength()
    {
        $encoder = new BCryptPasswordEncoder(self::VALID_COST);
        $encoder->encodePassword(str_repeat('a', 5000), 'salt');
    }
    public function testCheckPasswordLength()
    {
        $encoder = new BCryptPasswordEncoder(self::VALID_COST);
        $this->assertFalse($encoder->isPasswordValid('encoded', str_repeat('a', 5000), 'salt'));
    }
}
