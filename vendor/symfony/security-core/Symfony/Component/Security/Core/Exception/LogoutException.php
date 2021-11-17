<?php
namespace Symfony\Component\Security\Core\Exception;
class LogoutException extends \RuntimeException
{
    public function __construct($message = 'Logout Exception', \Exception $previous = null)
    {
        parent::__construct($message, 403, $previous);
    }
}
