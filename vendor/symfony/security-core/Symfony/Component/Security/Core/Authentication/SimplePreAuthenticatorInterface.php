<?php
namespace Symfony\Component\Security\Core\Authentication;
use Symfony\Component\HttpFoundation\Request;
interface SimplePreAuthenticatorInterface extends SimpleAuthenticatorInterface
{
    public function createToken(Request $request, $providerKey);
}
