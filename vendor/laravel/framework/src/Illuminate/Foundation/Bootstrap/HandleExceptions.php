<?php namespace Illuminate\Foundation\Bootstrap;
use ErrorException;
use Illuminate\Contracts\Foundation\Application;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Debug\Exception\FatalErrorException;
class HandleExceptions {
	protected $app;
	public function bootstrap(Application $app)
	{
		$this->app = $app;
		error_reporting(-1);
		set_error_handler([$this, 'handleError']);
		set_exception_handler([$this, 'handleException']);
		register_shutdown_function([$this, 'handleShutdown']);
		if ( ! $app->environment('testing'))
		{
			ini_set('display_errors', 'Off');
		}
	}
	public function handleError($level, $message, $file = '', $line = 0, $context = array())
	{
		if (error_reporting() & $level)
		{
			throw new ErrorException($message, 0, $level, $file, $line);
		}
	}
	public function handleException($e)
	{
		$this->getExceptionHandler()->report($e);
		if ($this->app->runningInConsole())
		{
			$this->renderForConsole($e);
		}
		else
		{
			$this->renderHttpResponse($e);
		}
	}
	protected function renderForConsole($e)
	{
		$this->getExceptionHandler()->renderForConsole(new ConsoleOutput, $e);
	}
	protected function renderHttpResponse($e)
	{
		$this->getExceptionHandler()->render($this->app['request'], $e)->send();
	}
	public function handleShutdown()
	{
		if ( ! is_null($error = error_get_last()) && $this->isFatal($error['type']))
		{
			$this->handleException($this->fatalExceptionFromError($error, 0));
		}
	}
	protected function fatalExceptionFromError(array $error, $traceOffset = null)
	{
		return new FatalErrorException(
			$error['message'], $error['type'], 0, $error['file'], $error['line'], $traceOffset
		);
	}
	protected function isFatal($type)
	{
		return in_array($type, [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE]);
	}
	protected function getExceptionHandler()
	{
		return $this->app->make('Illuminate\Contracts\Debug\ExceptionHandler');
	}
}
