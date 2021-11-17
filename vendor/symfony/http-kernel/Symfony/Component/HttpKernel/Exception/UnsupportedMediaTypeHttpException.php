<?php
namespace Symfony\Component\HttpKernel\Exception;
class UnsupportedMediaTypeHttpException extends HttpException
{
    public function __construct($message = null, \Exception $previous = null, $code = 0)
    {
        parent::__construct(415, $message, $previous, array(), $code);
    }
}
