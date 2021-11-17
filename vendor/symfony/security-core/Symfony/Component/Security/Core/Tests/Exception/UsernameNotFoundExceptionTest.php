<?php
namespace Symfony\Component\Security\Core\Tests\Exception;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
class UsernameNotFoundExceptionTest extends \PHPUnit_Framework_TestCase
{
    public function testGetMessageData()
    {
        $exception = new UsernameNotFoundException('Username could not be found.');
        $this->assertEquals(array('{{ username }}' => null), $exception->getMessageData());
        $exception->setUsername('username');
        $this->assertEquals(array('{{ username }}' => 'username'), $exception->getMessageData());
    }
}
