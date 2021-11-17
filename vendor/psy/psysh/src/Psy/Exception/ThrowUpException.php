<?php
namespace Psy\Exception;
class ThrowUpException extends \Exception implements Exception
{
    public function __construct(\Exception $exception)
    {
        $message = sprintf("Throwing %s with message '%s'", get_class($exception), $exception->getMessage());
        parent::__construct($message, $exception->getCode(), $exception);
    }
    public function getRawMessage()
    {
        return $this->getPrevious()->getMessage();
    }
}
