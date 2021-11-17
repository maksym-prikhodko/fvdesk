<?php
namespace Symfony\Component\HttpKernel\Exception;
class UnprocessableEntityHttpException extends HttpException
{
    public function __construct($message = null, \Exception $previous = null, $code = 0)
    {
        parent::__construct(422, $message, $previous, array(), $code);
    }
}
