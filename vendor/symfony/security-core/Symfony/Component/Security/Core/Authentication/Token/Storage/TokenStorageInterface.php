<?php
namespace Symfony\Component\Security\Core\Authentication\Token\Storage;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
interface TokenStorageInterface
{
    public function getToken();
    public function setToken(TokenInterface $token = null);
}
