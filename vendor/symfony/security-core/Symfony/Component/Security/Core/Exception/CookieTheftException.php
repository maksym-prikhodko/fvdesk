<?php
namespace Symfony\Component\Security\Core\Exception;
class CookieTheftException extends AuthenticationException
{
    public function getMessageKey()
    {
        return 'Cookie has already been used by someone else.';
    }
}
