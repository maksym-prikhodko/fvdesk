<?php
namespace Symfony\Component\Security\Core\Tests\Role;
use Symfony\Component\Security\Core\Role\Role;
class RoleTest extends \PHPUnit_Framework_TestCase
{
    public function testGetRole()
    {
        $role = new Role('FOO');
        $this->assertEquals('FOO', $role->getRole());
    }
}
