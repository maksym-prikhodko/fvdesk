<?php namespace Illuminate\Foundation\Exceptions;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Console\Application as ConsoleApplication;
use Symfony\Component\Debug\ExceptionHandler as SymfonyDisplayer;
use Illuminate\Contracts\Debug\ExceptionHandler as ExceptionHandlerContract;
class Handler implements ExceptionHandlerContract {
	protected $log;
	protected $dontReport = [];
	public function __construct(LoggerInterface $log)
	{
		$this->log = $log;
	}
	public function report(Exception $e)
	{
		if ($this->shouldntReport($e)) return;
		$this->log->error((string) $e);
	}
	public function shouldReport(Exception $e)
	{
		return ! $this->shouldntReport($e);
	}
	protected function shouldntReport(Exception $e)
	{
		foreach ($this->dontReport as $type)
		{
			if ($e instanceof $type) return true;
		}
		return false;
	}
	public function render($request, Exception $e)
	{
		if ($this->isHttpException($e))
		{
			return $this->renderHttpException($e);
		}
		else
		{
			return (new SymfonyDisplayer(config('app.debug')))->createResponse($e);
		}
	}
	public function renderForConsole($output, Exception $e)
	{
		(new ConsoleApplication)->renderException($e, $output);
	}
	protected function renderHttpException(HttpException $e)
	{
		$status = $e->getStatusCode();
		if (view()->exists("errors.{$status}"))
		{
			return response()->view("errors.{$status}", [], $status);
		}
		else
		{
			return (new SymfonyDisplayer(config('app.debug')))->createResponse($e);
		}
	}
	protected function isHttpException(Exception $e)
	{
		return $e instanceof HttpException;
	}
}
