<?php namespace Illuminate\Foundation\Bootstrap;
use Illuminate\Log\Writer;
use Monolog\Logger as Monolog;
use Illuminate\Contracts\Foundation\Application;
class ConfigureLogging {
	public function bootstrap(Application $app)
	{
		$this->configureHandlers($app, $this->registerLogger($app));
		$app->bind('Psr\Log\LoggerInterface', function($app)
		{
			return $app['log']->getMonolog();
		});
	}
	protected function registerLogger(Application $app)
	{
		$app->instance('log', $log = new Writer(
			new Monolog($app->environment()), $app['events'])
		);
		return $log;
	}
	protected function configureHandlers(Application $app, Writer $log)
	{
		$method = "configure".ucfirst($app['config']['app.log'])."Handler";
		$this->{$method}($app, $log);
	}
	protected function configureSingleHandler(Application $app, Writer $log)
	{
		$log->useFiles($app->storagePath().'/logs/laravel.log');
	}
	protected function configureDailyHandler(Application $app, Writer $log)
	{
		$log->useDailyFiles(
			$app->storagePath().'/logs/laravel.log',
			$app->make('config')->get('app.log_max_files', 5)
		);
	}
	protected function configureSyslogHandler(Application $app, Writer $log)
	{
		$log->useSyslog('laravel');
	}
	protected function configureErrorlogHandler(Application $app, Writer $log)
	{
		$log->useErrorLog();
	}
}
