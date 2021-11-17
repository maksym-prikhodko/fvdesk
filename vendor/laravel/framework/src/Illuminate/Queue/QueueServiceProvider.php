<?php namespace Illuminate\Queue;
use IlluminateQueueClosure;
use Illuminate\Support\ServiceProvider;
use Illuminate\Queue\Console\WorkCommand;
use Illuminate\Queue\Console\ListenCommand;
use Illuminate\Queue\Console\RestartCommand;
use Illuminate\Queue\Connectors\SqsConnector;
use Illuminate\Queue\Console\SubscribeCommand;
use Illuminate\Queue\Connectors\NullConnector;
use Illuminate\Queue\Connectors\SyncConnector;
use Illuminate\Queue\Connectors\IronConnector;
use Illuminate\Queue\Connectors\RedisConnector;
use Illuminate\Queue\Connectors\DatabaseConnector;
use Illuminate\Queue\Connectors\BeanstalkdConnector;
use Illuminate\Queue\Failed\DatabaseFailedJobProvider;
class QueueServiceProvider extends ServiceProvider {
	protected $defer = true;
	public function register()
	{
		$this->registerManager();
		$this->registerWorker();
		$this->registerListener();
		$this->registerSubscriber();
		$this->registerFailedJobServices();
		$this->registerQueueClosure();
	}
	protected function registerManager()
	{
		$this->app->singleton('queue', function($app)
		{
			$manager = new QueueManager($app);
			$this->registerConnectors($manager);
			return $manager;
		});
		$this->app->singleton('queue.connection', function($app)
		{
			return $app['queue']->connection();
		});
	}
	protected function registerWorker()
	{
		$this->registerWorkCommand();
		$this->registerRestartCommand();
		$this->app->singleton('queue.worker', function($app)
		{
			return new Worker($app['queue'], $app['queue.failer'], $app['events']);
		});
	}
	protected function registerWorkCommand()
	{
		$this->app->singleton('command.queue.work', function($app)
		{
			return new WorkCommand($app['queue.worker']);
		});
		$this->commands('command.queue.work');
	}
	protected function registerListener()
	{
		$this->registerListenCommand();
		$this->app->singleton('queue.listener', function($app)
		{
			return new Listener($app->basePath());
		});
	}
	protected function registerListenCommand()
	{
		$this->app->singleton('command.queue.listen', function($app)
		{
			return new ListenCommand($app['queue.listener']);
		});
		$this->commands('command.queue.listen');
	}
	public function registerRestartCommand()
	{
		$this->app->singleton('command.queue.restart', function()
		{
			return new RestartCommand;
		});
		$this->commands('command.queue.restart');
	}
	protected function registerSubscriber()
	{
		$this->app->singleton('command.queue.subscribe', function()
		{
			return new SubscribeCommand;
		});
		$this->commands('command.queue.subscribe');
	}
	public function registerConnectors($manager)
	{
		foreach (array('Null', 'Sync', 'Database', 'Beanstalkd', 'Redis', 'Sqs', 'Iron') as $connector)
		{
			$this->{"register{$connector}Connector"}($manager);
		}
	}
	protected function registerNullConnector($manager)
	{
		$manager->addConnector('null', function()
		{
			return new NullConnector;
		});
	}
	protected function registerSyncConnector($manager)
	{
		$manager->addConnector('sync', function()
		{
			return new SyncConnector;
		});
	}
	protected function registerBeanstalkdConnector($manager)
	{
		$manager->addConnector('beanstalkd', function()
		{
			return new BeanstalkdConnector;
		});
	}
	protected function registerDatabaseConnector($manager)
	{
		$manager->addConnector('database', function()
		{
			return new DatabaseConnector($this->app['db']);
		});
	}
	protected function registerRedisConnector($manager)
	{
		$app = $this->app;
		$manager->addConnector('redis', function() use ($app)
		{
			return new RedisConnector($app['redis']);
		});
	}
	protected function registerSqsConnector($manager)
	{
		$manager->addConnector('sqs', function()
		{
			return new SqsConnector;
		});
	}
	protected function registerIronConnector($manager)
	{
		$app = $this->app;
		$manager->addConnector('iron', function() use ($app)
		{
			return new IronConnector($app['encrypter'], $app['request']);
		});
		$this->registerIronRequestBinder();
	}
	protected function registerIronRequestBinder()
	{
		$this->app->rebinding('request', function($app, $request)
		{
			if ($app['queue']->connected('iron'))
			{
				$app['queue']->connection('iron')->setRequest($request);
			}
		});
	}
	protected function registerFailedJobServices()
	{
		$this->app->singleton('queue.failer', function($app)
		{
			$config = $app['config']['queue.failed'];
			return new DatabaseFailedJobProvider($app['db'], $config['database'], $config['table']);
		});
	}
	protected function registerQueueClosure()
	{
		$this->app->singleton('IlluminateQueueClosure', function($app)
		{
			return new IlluminateQueueClosure($app['encrypter']);
		});
	}
	public function provides()
	{
		return array(
			'queue', 'queue.worker', 'queue.listener', 'queue.failer',
			'command.queue.work', 'command.queue.listen', 'command.queue.restart',
			'command.queue.subscribe', 'queue.connection',
		);
	}
}
