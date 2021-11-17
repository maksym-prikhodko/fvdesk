<?php
namespace Symfony\Component\HttpKernel\Exception;
class FatalErrorException extends \ErrorException
{
}
namespace Symfony\Component\Debug\Exception;
use Symfony\Component\HttpKernel\Exception\FatalErrorException as LegacyFatalErrorException;
class FatalErrorException extends LegacyFatalErrorException
{
    public function __construct($message, $code, $severity, $filename, $lineno, $traceOffset = null, $traceArgs = true)
    {
        parent::__construct($message, $code, $severity, $filename, $lineno);
        if (null !== $traceOffset) {
            if (function_exists('xdebug_get_function_stack')) {
                $trace = xdebug_get_function_stack();
                if (0 < $traceOffset) {
                    array_splice($trace, -$traceOffset);
                }
                foreach ($trace as &$frame) {
                    if (!isset($frame['type'])) {
                        if (isset($frame['class'])) {
                            $frame['type'] = '::';
                        }
                    } elseif ('dynamic' === $frame['type']) {
                        $frame['type'] = '->';
                    } elseif ('static' === $frame['type']) {
                        $frame['type'] = '::';
                    }
                    if (!$traceArgs) {
                        unset($frame['params'], $frame['args']);
                    } elseif (isset($frame['params']) && !isset($frame['args'])) {
                        $frame['args'] = $frame['params'];
                        unset($frame['params']);
                    }
                }
                unset($frame);
                $trace = array_reverse($trace);
            } else {
                $trace = array();
            }
            $this->setTrace($trace);
        }
    }
    protected function setTrace($trace)
    {
        $traceReflector = new \ReflectionProperty('Exception', 'trace');
        $traceReflector->setAccessible(true);
        $traceReflector->setValue($this, $trace);
    }
}
