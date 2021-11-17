<?php
namespace Symfony\Component\Security\Core\Exception;
class AccessDeniedException extends \RuntimeException
{
    public function __construct($message = 'Access Denied', \Exception $previous = null)
    {
        parent::__construct($message, 403, $previous);
    }
}
