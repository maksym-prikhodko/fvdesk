<?php
namespace Symfony\Component\Security\Core\Exception;
class InsufficientAuthenticationException extends AuthenticationException
{
    public function getMessageKey()
    {
        return 'Not privileged to request the resource.';
    }
}
