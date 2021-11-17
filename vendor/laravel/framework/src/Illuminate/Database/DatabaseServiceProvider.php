<?php namespace Illuminate\Database;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\QueueEntityResolver;
use Illuminate\Database\Connectors\ConnectionFactory;
class DatabaseServiceProvider extends ServiceProvider {
	public function boot()
	{
		Model::setConnectionResolver($this->app['db']);
		Model::setEventDispatcher($this->app['events']);
	}
	public function register()
	{
		$this->registerQueueableEntityResolver();
		$this->app->singleton('db.factory', function($app)
		{
			return new ConnectionFactory($app);
		});
		$this->app->singleton('db', function($app)
		{
			return new DatabaseManager($app, $app['db.factory']);
		});
	}
	protected function registerQueueableEntityResolver()
	{
		$this->app->singleton('Illuminate\Contracts\Queue\EntityResolver', function()
		{
			return new QueueEntityResolver;
		});
	}
}
