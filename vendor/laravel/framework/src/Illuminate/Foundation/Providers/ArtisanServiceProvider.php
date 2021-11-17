<?php namespace Illuminate\Foundation\Providers;
use Illuminate\Support\ServiceProvider;
use Illuminate\Foundation\Console\UpCommand;
use Illuminate\Foundation\Console\DownCommand;
use Illuminate\Foundation\Console\FreshCommand;
use Illuminate\Foundation\Console\ServeCommand;
use Illuminate\Foundation\Console\TinkerCommand;
use Illuminate\Foundation\Console\AppNameCommand;
use Illuminate\Foundation\Console\OptimizeCommand;
use Illuminate\Foundation\Console\RouteListCommand;
use Illuminate\Foundation\Console\EventMakeCommand;
use Illuminate\Foundation\Console\ModelMakeCommand;
use Illuminate\Foundation\Console\RouteCacheCommand;
use Illuminate\Foundation\Console\RouteClearCommand;
use Illuminate\Foundation\Console\CommandMakeCommand;
use Illuminate\Foundation\Console\ConfigCacheCommand;
use Illuminate\Foundation\Console\ConfigClearCommand;
use Illuminate\Foundation\Console\ConsoleMakeCommand;
use Illuminate\Foundation\Console\EnvironmentCommand;
use Illuminate\Foundation\Console\KeyGenerateCommand;
use Illuminate\Foundation\Console\RequestMakeCommand;
use Illuminate\Foundation\Console\ProviderMakeCommand;
use Illuminate\Foundation\Console\HandlerEventCommand;
use Illuminate\Foundation\Console\ClearCompiledCommand;
use Illuminate\Foundation\Console\EventGenerateCommand;
use Illuminate\Foundation\Console\VendorPublishCommand;
use Illuminate\Foundation\Console\HandlerCommandCommand;
class ArtisanServiceProvider extends ServiceProvider {
	protected $defer = true;
	protected $commands = [
		'AppName' => 'command.app.name',
		'ClearCompiled' => 'command.clear-compiled',
		'CommandMake' => 'command.command.make',
		'ConfigCache' => 'command.config.cache',
		'ConfigClear' => 'command.config.clear',
		'ConsoleMake' => 'command.console.make',
		'EventGenerate' => 'command.event.generate',
		'EventMake' => 'command.event.make',
		'Down' => 'command.down',
		'Environment' => 'command.environment',
		'Fresh' => 'command.fresh',
		'HandlerCommand' => 'command.handler.command',
		'HandlerEvent' => 'command.handler.event',
		'KeyGenerate' => 'command.key.generate',
		'ModelMake' => 'command.model.make',
		'Optimize' => 'command.optimize',
		'ProviderMake' => 'command.provider.make',
		'RequestMake' => 'command.request.make',
		'RouteCache' => 'command.route.cache',
		'RouteClear' => 'command.route.clear',
		'RouteList' => 'command.route.list',
		'Serve' => 'command.serve',
		'Tinker' => 'command.tinker',
		'Up' => 'command.up',
		'VendorPublish' => 'command.vendor.publish',
	];
	public function register()
	{
		foreach (array_keys($this->commands) as $command)
		{
			$method = "register{$command}Command";
			call_user_func_array([$this, $method], []);
		}
		$this->commands(array_values($this->commands));
	}
	protected function registerAppNameCommand()
	{
		$this->app->singleton('command.app.name', function($app)
		{
			return new AppNameCommand($app['composer'], $app['files']);
		});
	}
	protected function registerClearCompiledCommand()
	{
		$this->app->singleton('command.clear-compiled', function()
		{
			return new ClearCompiledCommand;
		});
	}
	protected function registerCommandMakeCommand()
	{
		$this->app->singleton('command.command.make', function($app)
		{
			return new CommandMakeCommand($app['files']);
		});
	}
	protected function registerConfigCacheCommand()
	{
		$this->app->singleton('command.config.cache', function($app)
		{
			return new ConfigCacheCommand($app['files']);
		});
	}
	protected function registerConfigClearCommand()
	{
		$this->app->singleton('command.config.clear', function($app)
		{
			return new ConfigClearCommand($app['files']);
		});
	}
	protected function registerConsoleMakeCommand()
	{
		$this->app->singleton('command.console.make', function($app)
		{
			return new ConsoleMakeCommand($app['files']);
		});
	}
	protected function registerEventGenerateCommand()
	{
		$this->app->singleton('command.event.generate', function()
		{
			return new EventGenerateCommand;
		});
	}
	protected function registerEventMakeCommand()
	{
		$this->app->singleton('command.event.make', function($app)
		{
			return new EventMakeCommand($app['files']);
		});
	}
	protected function registerDownCommand()
	{
		$this->app->singleton('command.down', function()
		{
			return new DownCommand;
		});
	}
	protected function registerEnvironmentCommand()
	{
		$this->app->singleton('command.environment', function()
		{
			return new EnvironmentCommand;
		});
	}
	protected function registerFreshCommand()
	{
		$this->app->singleton('command.fresh', function()
		{
			return new FreshCommand;
		});
	}
	protected function registerHandlerCommandCommand()
	{
		$this->app->singleton('command.handler.command', function($app)
		{
			return new HandlerCommandCommand($app['files']);
		});
	}
	protected function registerHandlerEventCommand()
	{
		$this->app->singleton('command.handler.event', function($app)
		{
			return new HandlerEventCommand($app['files']);
		});
	}
	protected function registerKeyGenerateCommand()
	{
		$this->app->singleton('command.key.generate', function()
		{
			return new KeyGenerateCommand;
		});
	}
	protected function registerModelMakeCommand()
	{
		$this->app->singleton('command.model.make', function($app)
		{
			return new ModelMakeCommand($app['files']);
		});
	}
	protected function registerOptimizeCommand()
	{
		$this->app->singleton('command.optimize', function($app)
		{
			return new OptimizeCommand($app['composer']);
		});
	}
	protected function registerProviderMakeCommand()
	{
		$this->app->singleton('command.provider.make', function($app)
		{
			return new ProviderMakeCommand($app['files']);
		});
	}
	protected function registerRequestMakeCommand()
	{
		$this->app->singleton('command.request.make', function($app)
		{
			return new RequestMakeCommand($app['files']);
		});
	}
	protected function registerRouteCacheCommand()
	{
		$this->app->singleton('command.route.cache', function($app)
		{
			return new RouteCacheCommand($app['files']);
		});
	}
	protected function registerRouteClearCommand()
	{
		$this->app->singleton('command.route.clear', function($app)
		{
			return new RouteClearCommand($app['files']);
		});
	}
	protected function registerRouteListCommand()
	{
		$this->app->singleton('command.route.list', function($app)
		{
			return new RouteListCommand($app['router']);
		});
	}
	protected function registerServeCommand()
	{
		$this->app->singleton('command.serve', function()
		{
			return new ServeCommand;
		});
	}
	protected function registerTinkerCommand()
	{
		$this->app->singleton('command.tinker', function()
		{
			return new TinkerCommand;
		});
	}
	protected function registerUpCommand()
	{
		$this->app->singleton('command.up', function()
		{
			return new UpCommand;
		});
	}
	protected function registerVendorPublishCommand()
	{
		$this->app->singleton('command.vendor.publish', function($app)
		{
			return new VendorPublishCommand($app['files']);
		});
	}
	public function provides()
	{
		return array_values($this->commands);
	}
}
