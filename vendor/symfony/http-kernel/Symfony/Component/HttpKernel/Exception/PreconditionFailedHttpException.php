<?php
namespace Symfony\Component\HttpKernel\Exception;
class PreconditionFailedHttpException extends HttpException
{
    public function __construct($message = null, \Exception $previous = null, $code = 0)
    {
        parent::__construct(412, $message, $previous, array(), $code);
    }
}
