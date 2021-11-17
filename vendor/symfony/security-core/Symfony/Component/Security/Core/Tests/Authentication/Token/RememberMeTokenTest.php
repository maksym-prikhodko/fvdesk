<?php
namespace Symfony\Component\Security\Core\Tests\Authentication\Token;
use Symfony\Component\Security\Core\Authentication\Token\RememberMeToken;
use Symfony\Component\Security\Core\Role\Role;
class RememberMeTokenTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $user = $this->getUser();
        $token = new RememberMeToken($user, 'fookey', 'foo');
        $this->assertEquals('fookey', $token->getProviderKey());
        $this->assertEquals('foo', $token->getKey());
        $this->assertEquals(array(new Role('ROLE_FOO')), $token->getRoles());
        $this->assertSame($user, $token->getUser());
        $this->assertTrue($token->isAuthenticated());
    }
    public function testConstructorKeyCannotBeNull()
    {
        new RememberMeToken(
            $this->getUser(),
            null,
            null
        );
    }
    public function testConstructorKeyCannotBeEmptyString()
    {
        new RememberMeToken(
            $this->getUser(),
            '',
            ''
        );
    }
    protected function getUser($roles = array('ROLE_FOO'))
    {
        $user = $this->getMock('Symfony\Component\Security\Core\User\UserInterface');
        $user
            ->expects($this->once())
            ->method('getRoles')
            ->will($this->returnValue($roles))
        ;
        return $user;
    }
}
