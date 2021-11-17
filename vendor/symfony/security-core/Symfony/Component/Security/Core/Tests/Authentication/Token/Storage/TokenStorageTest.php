<?php
namespace Symfony\Component\Security\Core\Tests\Authentication\Token\Storage;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
class TokenStorageTest extends \PHPUnit_Framework_TestCase
{
    public function testGetSetToken()
    {
        $tokenStorage = new TokenStorage();
        $this->assertNull($tokenStorage->getToken());
        $token = $this->getMock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
        $tokenStorage->setToken($token);
        $this->assertSame($token, $tokenStorage->getToken());
    }
}
