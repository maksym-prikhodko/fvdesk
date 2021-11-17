<?php
namespace Symfony\Component\Security\Core\Exception;
class CredentialsExpiredException extends AccountStatusException
{
    public function getMessageKey()
    {
        return 'Credentials have expired.';
    }
}
