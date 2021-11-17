<?php
namespace Psy\Exception;
class ErrorException extends \ErrorException implements Exception
{
    private $rawMessage;
    public function __construct($message = "", $code = 0, $severity = 1, $filename = null, $lineno = null, $previous = null)
    {
        $this->rawMessage = $message;
        if (!empty($filename) && preg_match('{Psy[/\\\\]ExecutionLoop}', $filename)) {
            $filename = null;
        }
        switch ($severity) {
            case E_WARNING:
            case E_CORE_WARNING:
            case E_COMPILE_WARNING:
            case E_USER_WARNING:
                $type = 'warning';
                break;
            case E_STRICT:
                $type = 'Strict error';
                break;
            default:
                $type = 'error';
                break;
        }
        $message = sprintf('PHP %s:  %s%s on line %d', $type, $message, $filename ? ' in ' . $filename : '', $lineno);
        parent::__construct($message, $code, $severity, $filename, $lineno, $previous);
    }
    public function getRawMessage()
    {
        return $this->rawMessage;
    }
    public static function throwException($errno, $errstr, $errfile, $errline)
    {
        throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
    }
}
