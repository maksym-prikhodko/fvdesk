<?php namespace Illuminate\Foundation\Console;
use Exception;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Console\Application as Artisan;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Console\Kernel as KernelContract;
class Kernel implements KernelContract {
	protected $app;
	protected $events;
	protected $artisan;
	protected $bootstrappers = [
		'Illuminate\Foundation\Bootstrap\DetectEnvironment',
		'Illuminate\Foundation\Bootstrap\LoadConfiguration',
		'Illuminate\Foundation\Bootstrap\ConfigureLogging',
		'Illuminate\Foundation\Bootstrap\HandleExceptions',
		'Illuminate\Foundation\Bootstrap\RegisterFacades',
		'Illuminate\Foundation\Bootstrap\SetRequestForConsole',
		'Illuminate\Foundation\Bootstrap\RegisterProviders',
		'Illuminate\Foundation\Bootstrap\BootProviders',
	];
	public function __construct(Application $app, Dispatcher $events)
	{
		$this->app = $app;
		$this->events = $events;
		$this->app->booted(function()
		{
			$this->defineConsoleSchedule();
		});
	}
	protected function defineConsoleSchedule()
	{
		$this->app->instance(
			'Illuminate\Console\Scheduling\Schedule', $schedule = new Schedule
		);
		$this->schedule($schedule);
	}
	public function handle($input, $output = null)
	{
		try
		{
			$this->bootstrap();
			return $this->getArtisan()->run($input, $output);
		}
		catch (Exception $e)
		{
			$this->reportException($e);
			$this->renderException($output, $e);
			return 1;
		}
	}
	public function terminate($input, $status)
	{
		$this->app->terminate();
	}
	protected function schedule(Schedule $schedule)
	{
	}
	public function call($command, array $parameters = array())
	{
		$this->bootstrap();
		$this->app->loadDeferredProviders();
		return $this->getArtisan()->call($command, $parameters);
	}
	public function queue($command, array $parameters = array())
	{
		$this->app['Illuminate\Contracts\Queue\Queue']->push(
			'Illuminate\Foundation\Console\QueuedJob', func_get_args()
		);
	}
	public function all()
	{
		$this->bootstrap();
		return $this->getArtisan()->all();
	}
	public function output()
	{
		$this->bootstrap();
		return $this->getArtisan()->output();
	}
	public function bootstrap()
	{
		if ( ! $this->app->hasBeenBootstrapped())
		{
			$this->app->bootstrapWith($this->bootstrappers());
		}
		$this->app->loadDeferredProviders();
	}
	protected function getArtisan()
	{
		if (is_null($this->artisan))
		{
			return $this->artisan = (new Artisan($this->app, $this->events, $this->app->version()))
								->resolveCommands($this->commands);
		}
		return $this->artisan;
	}
	protected function bootstrappers()
	{
		return $this->bootstrappers;
	}
	protected function reportException(Exception $e)
	{
		$this->app['Illuminate\Contracts\Debug\ExceptionHandler']->report($e);
	}
	protected function renderException($output, Exception $e)
	{
		$this->app['Illuminate\Contracts\Debug\ExceptionHandler']->renderForConsole($output, $e);
	}
}
