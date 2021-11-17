<?php
namespace Symfony\Component\Security\Core\Exception;
class SessionUnavailableException extends AuthenticationException
{
    public function getMessageKey()
    {
        return 'No session available, it either timed out or cookies are not enabled.';
    }
}
