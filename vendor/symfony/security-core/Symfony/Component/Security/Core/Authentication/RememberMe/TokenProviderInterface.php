<?php
namespace Symfony\Component\Security\Core\Authentication\RememberMe;
use Symfony\Component\Security\Core\Exception\TokenNotFoundException;
interface TokenProviderInterface
{
    public function loadTokenBySeries($series);
    public function deleteTokenBySeries($series);
    public function updateToken($series, $tokenValue, \DateTime $lastUsed);
    public function createNewToken(PersistentTokenInterface $token);
}
