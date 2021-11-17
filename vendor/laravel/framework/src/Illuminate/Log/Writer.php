<?php namespace Illuminate\Log;
use Closure;
use RuntimeException;
use InvalidArgumentException;
use Monolog\Handler\SyslogHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger as MonologLogger;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Handler\RotatingFileHandler;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Support\Arrayable;
use Psr\Log\LoggerInterface as PsrLoggerInterface;
use Illuminate\Contracts\Logging\Log as LogContract;
class Writer implements LogContract, PsrLoggerInterface {
	protected $monolog;
	protected $dispatcher;
	protected $levels = [
		'debug'     => MonologLogger::DEBUG,
		'info'      => MonologLogger::INFO,
		'notice'    => MonologLogger::NOTICE,
		'warning'   => MonologLogger::WARNING,
		'error'     => MonologLogger::ERROR,
		'critical'  => MonologLogger::CRITICAL,
		'alert'     => MonologLogger::ALERT,
		'emergency' => MonologLogger::EMERGENCY,
	];
	public function __construct(MonologLogger $monolog, Dispatcher $dispatcher = null)
	{
		$this->monolog = $monolog;
		if (isset($dispatcher))
		{
			$this->dispatcher = $dispatcher;
		}
	}
	public function emergency($message, array $context = array())
	{
		return $this->writeLog(__FUNCTION__, $message, $context);
	}
	public function alert($message, array $context = array())
	{
		return $this->writeLog(__FUNCTION__, $message, $context);
	}
	public function critical($message, array $context = array())
	{
		return $this->writeLog(__FUNCTION__, $message, $context);
	}
	public function error($message, array $context = array())
	{
		return $this->writeLog(__FUNCTION__, $message, $context);
	}
	public function warning($message, array $context = array())
	{
		return $this->writeLog(__FUNCTION__, $message, $context);
	}
	public function notice($message, array $context = array())
	{
		return $this->writeLog(__FUNCTION__, $message, $context);
	}
	public function info($message, array $context = array())
	{
		return $this->writeLog(__FUNCTION__, $message, $context);
	}
	public function debug($message, array $context = array())
	{
		return $this->writeLog(__FUNCTION__, $message, $context);
	}
	public function log($level, $message, array $context = array())
	{
		return $this->writeLog($level, $message, $context);
	}
	public function write($level, $message, array $context = array())
	{
		return $this->writeLog($level, $message, $context);
	}
	protected function writeLog($level, $message, $context)
	{
		$this->fireLogEvent($level, $message = $this->formatMessage($message), $context);
		$this->monolog->{$level}($message, $context);
	}
	public function useFiles($path, $level = 'debug')
	{
		$this->monolog->pushHandler($handler = new StreamHandler($path, $this->parseLevel($level)));
		$handler->setFormatter($this->getDefaultFormatter());
	}
	public function useDailyFiles($path, $days = 0, $level = 'debug')
	{
		$this->monolog->pushHandler(
			$handler = new RotatingFileHandler($path, $days, $this->parseLevel($level))
		);
		$handler->setFormatter($this->getDefaultFormatter());
	}
	public function useSyslog($name = 'laravel', $level = 'debug')
	{
		return $this->monolog->pushHandler(new SyslogHandler($name, LOG_USER, $level));
	}
	public function useErrorLog($level = 'debug', $messageType = ErrorLogHandler::OPERATING_SYSTEM)
	{
		$this->monolog->pushHandler(
			$handler = new ErrorLogHandler($messageType, $this->parseLevel($level))
		);
		$handler->setFormatter($this->getDefaultFormatter());
	}
	public function listen(Closure $callback)
	{
		if ( ! isset($this->dispatcher))
		{
			throw new RuntimeException("Events dispatcher has not been set.");
		}
		$this->dispatcher->listen('illuminate.log', $callback);
	}
	protected function fireLogEvent($level, $message, array $context = array())
	{
		if (isset($this->dispatcher))
		{
			$this->dispatcher->fire('illuminate.log', compact('level', 'message', 'context'));
		}
	}
	protected function formatMessage($message)
	{
		if (is_array($message))
		{
			return var_export($message, true);
		}
		elseif ($message instanceof Jsonable)
		{
			return $message->toJson();
		}
		elseif ($message instanceof Arrayable)
		{
			return var_export($message->toArray(), true);
		}
		return $message;
	}
	protected function parseLevel($level)
	{
		if (isset($this->levels[$level]))
		{
			return $this->levels[$level];
		}
		throw new InvalidArgumentException("Invalid log level.");
	}
	public function getMonolog()
	{
		return $this->monolog;
	}
	protected function getDefaultFormatter()
	{
		return new LineFormatter(null, null, true, true);
	}
	public function getEventDispatcher()
	{
		return $this->dispatcher;
	}
	public function setEventDispatcher(Dispatcher $dispatcher)
	{
		$this->dispatcher = $dispatcher;
	}
}
