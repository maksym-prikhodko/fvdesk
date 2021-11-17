<?php
namespace Symfony\Component\Security\Core\Exception;
class AuthenticationServiceException extends AuthenticationException
{
    public function getMessageKey()
    {
        return 'Authentication request could not be processed due to a system problem.';
    }
}
