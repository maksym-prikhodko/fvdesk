<?php
namespace Symfony\Component\Debug;
use Psr\Log\LogLevel;
use Psr\Log\LoggerInterface;
use Symfony\Component\Debug\Exception\ContextErrorException;
use Symfony\Component\Debug\Exception\FatalErrorException;
use Symfony\Component\Debug\Exception\OutOfMemoryException;
use Symfony\Component\Debug\FatalErrorHandler\UndefinedFunctionFatalErrorHandler;
use Symfony\Component\Debug\FatalErrorHandler\UndefinedMethodFatalErrorHandler;
use Symfony\Component\Debug\FatalErrorHandler\ClassNotFoundFatalErrorHandler;
use Symfony\Component\Debug\FatalErrorHandler\FatalErrorHandlerInterface;
class ErrorHandler
{
    const TYPE_DEPRECATION = -100;
    private $levels = array(
        E_DEPRECATED => 'Deprecated',
        E_USER_DEPRECATED => 'User Deprecated',
        E_NOTICE => 'Notice',
        E_USER_NOTICE => 'User Notice',
        E_STRICT => 'Runtime Notice',
        E_WARNING => 'Warning',
        E_USER_WARNING => 'User Warning',
        E_COMPILE_WARNING => 'Compile Warning',
        E_CORE_WARNING => 'Core Warning',
        E_USER_ERROR => 'User Error',
        E_RECOVERABLE_ERROR => 'Catchable Fatal Error',
        E_COMPILE_ERROR => 'Compile Error',
        E_PARSE => 'Parse Error',
        E_ERROR => 'Error',
        E_CORE_ERROR => 'Core Error',
    );
    private $loggers = array(
        E_DEPRECATED => array(null, LogLevel::INFO),
        E_USER_DEPRECATED => array(null, LogLevel::INFO),
        E_NOTICE => array(null, LogLevel::NOTICE),
        E_USER_NOTICE => array(null, LogLevel::NOTICE),
        E_STRICT => array(null, LogLevel::NOTICE),
        E_WARNING => array(null, LogLevel::WARNING),
        E_USER_WARNING => array(null, LogLevel::WARNING),
        E_COMPILE_WARNING => array(null, LogLevel::WARNING),
        E_CORE_WARNING => array(null, LogLevel::WARNING),
        E_USER_ERROR => array(null, LogLevel::ERROR),
        E_RECOVERABLE_ERROR => array(null, LogLevel::ERROR),
        E_COMPILE_ERROR => array(null, LogLevel::EMERGENCY),
        E_PARSE => array(null, LogLevel::EMERGENCY),
        E_ERROR => array(null, LogLevel::EMERGENCY),
        E_CORE_ERROR => array(null, LogLevel::EMERGENCY),
    );
    private $thrownErrors = 0x1FFF; 
    private $scopedErrors = 0x1FFF; 
    private $tracedErrors = 0x77FB; 
    private $screamedErrors = 0x55; 
    private $loggedErrors = 0;
    private $loggedTraces = array();
    private $isRecursive = 0;
    private $exceptionHandler;
    private static $reservedMemory;
    private static $stackedErrors = array();
    private static $stackedErrorLevels = array();
    private $displayErrors = 0x1FFF;
    public static function register($handler = null, $replace = true)
    {
        if (null === self::$reservedMemory) {
            self::$reservedMemory = str_repeat('x', 10240);
            register_shutdown_function(__CLASS__.'::handleFatalError');
        }
        $levels = -1;
        if ($handlerIsNew = !$handler instanceof self) {
            if (null !== $handler) {
                $levels = $replace ? $handler : 0;
                $replace = true;
            }
            $handler = new static();
        }
        $prev = set_error_handler(array($handler, 'handleError'), $handler->thrownErrors | $handler->loggedErrors);
        if ($handlerIsNew && is_array($prev) && $prev[0] instanceof self) {
            $handler = $prev[0];
            $replace = false;
        }
        if ($replace || !$prev) {
            $handler->setExceptionHandler(set_exception_handler(array($handler, 'handleException')));
        } else {
            restore_error_handler();
        }
        $handler->throwAt($levels & $handler->thrownErrors, true);
        return $handler;
    }
    public function setDefaultLogger(LoggerInterface $logger, $levels = null, $replace = false)
    {
        $loggers = array();
        if (is_array($levels)) {
            foreach ($levels as $type => $logLevel) {
                if (empty($this->loggers[$type][0]) || $replace) {
                    $loggers[$type] = array($logger, $logLevel);
                }
            }
        } else {
            if (null === $levels) {
                $levels = E_ALL | E_STRICT;
            }
            foreach ($this->loggers as $type => $log) {
                if (($type & $levels) && (empty($log[0]) || $replace)) {
                    $log[0] = $logger;
                    $loggers[$type] = $log;
                }
            }
        }
        $this->setLoggers($loggers);
    }
    public function setLoggers(array $loggers)
    {
        $prevLogged = $this->loggedErrors;
        $prev = $this->loggers;
        foreach ($loggers as $type => $log) {
            if (!isset($prev[$type])) {
                throw new \InvalidArgumentException('Unknown error type: '.$type);
            }
            if (!is_array($log)) {
                $log = array($log);
            } elseif (!array_key_exists(0, $log)) {
                throw new \InvalidArgumentException('No logger provided');
            }
            if (null === $log[0]) {
                $this->loggedErrors &= ~$type;
            } elseif ($log[0] instanceof LoggerInterface) {
                $this->loggedErrors |= $type;
            } else {
                throw new \InvalidArgumentException('Invalid logger provided');
            }
            $this->loggers[$type] = $log + $prev[$type];
        }
        $this->reRegister($prevLogged | $this->thrownErrors);
        return $prev;
    }
    public function setExceptionHandler($handler)
    {
        if (null !== $handler && !is_callable($handler)) {
            throw new \LogicException('The exception handler must be a valid PHP callable.');
        }
        $prev = $this->exceptionHandler;
        $this->exceptionHandler = $handler;
        return $prev;
    }
    public function throwAt($levels, $replace = false)
    {
        $prev = $this->thrownErrors;
        $this->thrownErrors = ($levels | E_RECOVERABLE_ERROR | E_USER_ERROR) & ~E_USER_DEPRECATED & ~E_DEPRECATED;
        if (!$replace) {
            $this->thrownErrors |= $prev;
        }
        $this->reRegister($prev | $this->loggedErrors);
        $this->displayErrors = $this->thrownErrors;
        return $prev;
    }
    public function scopeAt($levels, $replace = false)
    {
        $prev = $this->scopedErrors;
        $this->scopedErrors = (int) $levels;
        if (!$replace) {
            $this->scopedErrors |= $prev;
        }
        return $prev;
    }
    public function traceAt($levels, $replace = false)
    {
        $prev = $this->tracedErrors;
        $this->tracedErrors = (int) $levels;
        if (!$replace) {
            $this->tracedErrors |= $prev;
        }
        return $prev;
    }
    public function screamAt($levels, $replace = false)
    {
        $prev = $this->screamedErrors;
        $this->screamedErrors = (int) $levels;
        if (!$replace) {
            $this->screamedErrors |= $prev;
        }
        return $prev;
    }
    private function reRegister($prev)
    {
        if ($prev !== $this->thrownErrors | $this->loggedErrors) {
            $handler = set_error_handler('var_dump', 0);
            $handler = is_array($handler) ? $handler[0] : null;
            restore_error_handler();
            if ($handler === $this) {
                restore_error_handler();
                set_error_handler(array($this, 'handleError'), $this->thrownErrors | $this->loggedErrors);
            }
        }
    }
    public function handleError($type, $message, $file, $line, array $context)
    {
        $level = error_reporting() | E_RECOVERABLE_ERROR | E_USER_ERROR;
        $log = $this->loggedErrors & $type;
        $throw = $this->thrownErrors & $type & $level;
        $type &= $level | $this->screamedErrors;
        if ($type && ($log || $throw)) {
            if (PHP_VERSION_ID < 50400 && isset($context['GLOBALS']) && ($this->scopedErrors & $type)) {
                $e = $context;                  
                unset($e['GLOBALS'], $context); 
                $context = $e;
            }
            if ($throw) {
                if (($this->scopedErrors & $type) && class_exists('Symfony\Component\Debug\Exception\ContextErrorException')) {
                    $throw = new ContextErrorException($this->levels[$type].': '.$message, 0, $type, $file, $line, $context);
                } else {
                    $throw = new \ErrorException($this->levels[$type].': '.$message, 0, $type, $file, $line);
                }
                if (PHP_VERSION_ID <= 50407 && (PHP_VERSION_ID >= 50400 || PHP_VERSION_ID <= 50317)) {
                    $throw->errorHandlerCanary = new ErrorHandlerCanary();
                }
                throw $throw;
            }
            $e = md5("{$type}/{$line}/{$file}\x00{$message}", true);
            $trace = true;
            if (!($this->tracedErrors & $type) || isset($this->loggedTraces[$e])) {
                $trace = false;
            } else {
                $this->loggedTraces[$e] = 1;
            }
            $e = compact('type', 'file', 'line', 'level');
            if ($type & $level) {
                if ($this->scopedErrors & $type) {
                    $e['context'] = $context;
                    if ($trace) {
                        $e['stack'] = debug_backtrace(true); 
                    }
                } elseif ($trace) {
                    $e['stack'] = debug_backtrace(PHP_VERSION_ID >= 50306 ? DEBUG_BACKTRACE_IGNORE_ARGS : false);
                }
            }
            if ($this->isRecursive) {
                $log = 0;
            } elseif (self::$stackedErrorLevels) {
                self::$stackedErrors[] = array($this->loggers[$type], $message, $e);
            } else {
                try {
                    $this->isRecursive = true;
                    $this->loggers[$type][0]->log($this->loggers[$type][1], $message, $e);
                    $this->isRecursive = false;
                } catch (\Exception $e) {
                    $this->isRecursive = false;
                    throw $e;
                }
            }
        }
        return $type && $log;
    }
    public function handleException(\Exception $exception, array $error = null)
    {
        $level = error_reporting();
        if ($this->loggedErrors & E_ERROR & ($level | $this->screamedErrors)) {
            $e = array(
                'type' => E_ERROR,
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'level' => $level,
                'stack' => $exception->getTrace(),
            );
            if ($exception instanceof FatalErrorException) {
                $message = 'Fatal '.$exception->getMessage();
            } elseif ($exception instanceof \ErrorException) {
                $message = 'Uncaught '.$exception->getMessage();
                if ($exception instanceof ContextErrorException) {
                    $e['context'] = $exception->getContext();
                }
            } else {
                $message = 'Uncaught Exception: '.$exception->getMessage();
            }
            if ($this->loggedErrors & $e['type']) {
                $this->loggers[$e['type']][0]->log($this->loggers[$e['type']][1], $message, $e);
            }
        }
        if ($exception instanceof FatalErrorException && !$exception instanceof OutOfMemoryException && $error) {
            foreach ($this->getFatalErrorHandlers() as $handler) {
                if ($e = $handler->handleError($error, $exception)) {
                    $exception = $e;
                    break;
                }
            }
        }
        if (empty($this->exceptionHandler)) {
            throw $exception; 
        }
        try {
            call_user_func($this->exceptionHandler, $exception);
        } catch (\Exception $handlerException) {
            $this->exceptionHandler = null;
            $this->handleException($handlerException);
        }
    }
    public static function handleFatalError(array $error = null)
    {
        self::$reservedMemory = '';
        $handler = set_error_handler('var_dump', 0);
        $handler = is_array($handler) ? $handler[0] : null;
        restore_error_handler();
        if ($handler instanceof self) {
            if (null === $error) {
                $error = error_get_last();
            }
            try {
                while (self::$stackedErrorLevels) {
                    static::unstackErrors();
                }
            } catch (\Exception $exception) {
            }
            if ($error && ($error['type'] & (E_PARSE | E_ERROR | E_CORE_ERROR | E_COMPILE_ERROR))) {
                $handler->throwAt(0, true);
                if (0 === strpos($error['message'], 'Allowed memory') || 0 === strpos($error['message'], 'Out of memory')) {
                    $exception = new OutOfMemoryException($handler->levels[$error['type']].': '.$error['message'], 0, $error['type'], $error['file'], $error['line'], 2, false);
                } else {
                    $exception = new FatalErrorException($handler->levels[$error['type']].': '.$error['message'], 0, $error['type'], $error['file'], $error['line'], 2, true);
                }
            } elseif (!isset($exception)) {
                return;
            }
            try {
                $handler->handleException($exception, $error);
            } catch (FatalErrorException $e) {
            }
        }
    }
    public static function stackErrors()
    {
        self::$stackedErrorLevels[] = error_reporting(error_reporting() | E_PARSE | E_ERROR | E_CORE_ERROR | E_COMPILE_ERROR);
    }
    public static function unstackErrors()
    {
        $level = array_pop(self::$stackedErrorLevels);
        if (null !== $level) {
            $e = error_reporting($level);
            if ($e !== ($level | E_PARSE | E_ERROR | E_CORE_ERROR | E_COMPILE_ERROR)) {
                error_reporting($e);
            }
        }
        if (empty(self::$stackedErrorLevels)) {
            $errors = self::$stackedErrors;
            self::$stackedErrors = array();
            foreach ($errors as $e) {
                $e[0][0]->log($e[0][1], $e[1], $e[2]);
            }
        }
    }
    protected function getFatalErrorHandlers()
    {
        return array(
            new UndefinedFunctionFatalErrorHandler(),
            new UndefinedMethodFatalErrorHandler(),
            new ClassNotFoundFatalErrorHandler(),
        );
    }
    public function setLevel($level)
    {
        $level = null === $level ? error_reporting() : $level;
        $this->throwAt($level, true);
    }
    public function setDisplayErrors($displayErrors)
    {
        if ($displayErrors) {
            $this->throwAt($this->displayErrors, true);
        } else {
            $displayErrors = $this->displayErrors;
            $this->throwAt(0, true);
            $this->displayErrors = $displayErrors;
        }
    }
    public static function setLogger(LoggerInterface $logger, $channel = 'deprecation')
    {
        $handler = set_error_handler('var_dump', 0);
        $handler = is_array($handler) ? $handler[0] : null;
        restore_error_handler();
        if (!$handler instanceof self) {
            return;
        }
        if ('deprecation' === $channel) {
            $handler->setDefaultLogger($logger, E_DEPRECATED | E_USER_DEPRECATED, true);
            $handler->screamAt(E_DEPRECATED | E_USER_DEPRECATED);
        } elseif ('scream' === $channel) {
            $handler->setDefaultLogger($logger, E_ALL | E_STRICT, false);
            $handler->screamAt(E_ALL | E_STRICT);
        } elseif ('emergency' === $channel) {
            $handler->setDefaultLogger($logger, E_PARSE | E_ERROR | E_CORE_ERROR | E_COMPILE_ERROR, true);
            $handler->screamAt(E_PARSE | E_ERROR | E_CORE_ERROR | E_COMPILE_ERROR);
        }
    }
    public function handle($level, $message, $file = 'unknown', $line = 0, $context = array())
    {
        return $this->handleError($level, $message, $file, $line, (array) $context);
    }
    public function handleFatal()
    {
        static::handleFatalError();
    }
}
class ErrorHandlerCanary
{
    private static $displayErrors = null;
    public function __construct()
    {
        if (null === self::$displayErrors) {
            self::$displayErrors = ini_set('display_errors', 1);
        }
    }
    public function __destruct()
    {
        if (null !== self::$displayErrors) {
            ini_set('display_errors', self::$displayErrors);
            self::$displayErrors = null;
        }
    }
}
