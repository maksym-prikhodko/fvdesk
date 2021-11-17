<?php
namespace Symfony\Component\Security\Core\Exception;
class ProviderNotFoundException extends AuthenticationException
{
    public function getMessageKey()
    {
        return 'No authentication provider found to support the authentication token.';
    }
}
