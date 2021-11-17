<?php
namespace Symfony\Component\Security\Core\Tests\Role;
use Symfony\Component\Security\Core\Role\SwitchUserRole;
class SwitchUserRoleTest extends \PHPUnit_Framework_TestCase
{
    public function testGetSource()
    {
        $role = new SwitchUserRole('FOO', $token = $this->getMock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface'));
        $this->assertSame($token, $role->getSource());
    }
    public function testGetRole()
    {
        $role = new SwitchUserRole('FOO', $this->getMock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface'));
        $this->assertEquals('FOO', $role->getRole());
    }
}
