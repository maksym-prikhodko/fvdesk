<?php
namespace Symfony\Component\Security\Core\Tests\User;
use Symfony\Component\Security\Core\User\UserChecker;
class UserCheckerTest extends \PHPUnit_Framework_TestCase
{
    public function testCheckPostAuthNotAdvancedUserInterface()
    {
        $checker = new UserChecker();
        $this->assertNull($checker->checkPostAuth($this->getMock('Symfony\Component\Security\Core\User\UserInterface')));
    }
    public function testCheckPostAuthPass()
    {
        $checker = new UserChecker();
        $account = $this->getMock('Symfony\Component\Security\Core\User\AdvancedUserInterface');
        $account->expects($this->once())->method('isCredentialsNonExpired')->will($this->returnValue(true));
        $this->assertNull($checker->checkPostAuth($account));
    }
    public function testCheckPostAuthCredentialsExpired()
    {
        $checker = new UserChecker();
        $account = $this->getMock('Symfony\Component\Security\Core\User\AdvancedUserInterface');
        $account->expects($this->once())->method('isCredentialsNonExpired')->will($this->returnValue(false));
        $checker->checkPostAuth($account);
    }
    public function testCheckPreAuthNotAdvancedUserInterface()
    {
        $checker = new UserChecker();
        $this->assertNull($checker->checkPreAuth($this->getMock('Symfony\Component\Security\Core\User\UserInterface')));
    }
    public function testCheckPreAuthPass()
    {
        $checker = new UserChecker();
        $account = $this->getMock('Symfony\Component\Security\Core\User\AdvancedUserInterface');
        $account->expects($this->once())->method('isAccountNonLocked')->will($this->returnValue(true));
        $account->expects($this->once())->method('isEnabled')->will($this->returnValue(true));
        $account->expects($this->once())->method('isAccountNonExpired')->will($this->returnValue(true));
        $this->assertNull($checker->checkPreAuth($account));
    }
    public function testCheckPreAuthAccountLocked()
    {
        $checker = new UserChecker();
        $account = $this->getMock('Symfony\Component\Security\Core\User\AdvancedUserInterface');
        $account->expects($this->once())->method('isAccountNonLocked')->will($this->returnValue(false));
        $checker->checkPreAuth($account);
    }
    public function testCheckPreAuthDisabled()
    {
        $checker = new UserChecker();
        $account = $this->getMock('Symfony\Component\Security\Core\User\AdvancedUserInterface');
        $account->expects($this->once())->method('isAccountNonLocked')->will($this->returnValue(true));
        $account->expects($this->once())->method('isEnabled')->will($this->returnValue(false));
        $checker->checkPreAuth($account);
    }
    public function testCheckPreAuthAccountExpired()
    {
        $checker = new UserChecker();
        $account = $this->getMock('Symfony\Component\Security\Core\User\AdvancedUserInterface');
        $account->expects($this->once())->method('isAccountNonLocked')->will($this->returnValue(true));
        $account->expects($this->once())->method('isEnabled')->will($this->returnValue(true));
        $account->expects($this->once())->method('isAccountNonExpired')->will($this->returnValue(false));
        $checker->checkPreAuth($account);
    }
}
