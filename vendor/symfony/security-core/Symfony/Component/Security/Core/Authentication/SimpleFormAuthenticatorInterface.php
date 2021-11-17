<?php
namespace Symfony\Component\Security\Core\Authentication;
use Symfony\Component\HttpFoundation\Request;
interface SimpleFormAuthenticatorInterface extends SimpleAuthenticatorInterface
{
    public function createToken(Request $request, $username, $password, $providerKey);
}
