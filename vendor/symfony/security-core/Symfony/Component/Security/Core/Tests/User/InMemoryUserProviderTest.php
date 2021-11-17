<?php
namespace Symfony\Component\Security\Core\Tests\User;
use Symfony\Component\Security\Core\User\InMemoryUserProvider;
use Symfony\Component\Security\Core\User\User;
class InMemoryUserProviderTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $provider = new InMemoryUserProvider(array(
            'fabien' => array(
                'password' => 'foo',
                'enabled' => false,
                'roles' => array('ROLE_USER'),
            ),
        ));
        $user = $provider->loadUserByUsername('fabien');
        $this->assertEquals('foo', $user->getPassword());
        $this->assertEquals(array('ROLE_USER'), $user->getRoles());
        $this->assertFalse($user->isEnabled());
    }
    public function testCreateUser()
    {
        $provider = new InMemoryUserProvider();
        $provider->createUser(new User('fabien', 'foo'));
        $user = $provider->loadUserByUsername('fabien');
        $this->assertEquals('foo', $user->getPassword());
    }
    public function testCreateUserAlreadyExist()
    {
        $provider = new InMemoryUserProvider();
        $provider->createUser(new User('fabien', 'foo'));
        $provider->createUser(new User('fabien', 'foo'));
    }
    public function testLoadUserByUsernameDoesNotExist()
    {
        $provider = new InMemoryUserProvider();
        $provider->loadUserByUsername('fabien');
    }
}
