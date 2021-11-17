<?php
namespace Symfony\Component\Security\Core\Tests\Authorization;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
class AuthorizationCheckerTest extends \PHPUnit_Framework_TestCase
{
    private $authenticationManager;
    private $accessDecisionManager;
    private $authorizationChecker;
    private $tokenStorage;
    protected function setUp()
    {
        $this->authenticationManager = $this->getMock('Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface');
        $this->accessDecisionManager = $this->getMock('Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface');
        $this->tokenStorage = new TokenStorage();
        $this->authorizationChecker = new AuthorizationChecker(
            $this->tokenStorage,
            $this->authenticationManager,
            $this->accessDecisionManager
        );
    }
    public function testVoteAuthenticatesTokenIfNecessary()
    {
        $token = $this->getMock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
        $this->tokenStorage->setToken($token);
        $newToken = $this->getMock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
        $this->authenticationManager
            ->expects($this->once())
            ->method('authenticate')
            ->with($this->equalTo($token))
            ->will($this->returnValue($newToken));
        $tokenComparison = function ($value) use ($newToken) {
            return $value === $newToken;
        };
        $this->accessDecisionManager
            ->expects($this->once())
            ->method('decide')
            ->with($this->callback($tokenComparison))
            ->will($this->returnValue(true));
        $this->assertFalse($newToken === $this->tokenStorage->getToken());
        $this->assertTrue($this->authorizationChecker->isGranted('foo'));
        $this->assertTrue($newToken === $this->tokenStorage->getToken());
    }
    public function testVoteWithoutAuthenticationToken()
    {
        $this->authorizationChecker->isGranted('ROLE_FOO');
    }
    public function testIsGranted($decide)
    {
        $token = $this->getMock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
        $token
            ->expects($this->once())
            ->method('isAuthenticated')
            ->will($this->returnValue(true));
        $this->accessDecisionManager
            ->expects($this->once())
            ->method('decide')
            ->will($this->returnValue($decide));
        $this->tokenStorage->setToken($token);
        $this->assertTrue($decide === $this->authorizationChecker->isGranted('ROLE_FOO'));
    }
    public function isGrantedProvider()
    {
        return array(array(true), array(false));
    }
}
