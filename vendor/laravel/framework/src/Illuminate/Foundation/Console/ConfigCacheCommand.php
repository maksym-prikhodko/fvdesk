<?php namespace Illuminate\Foundation\Console;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
class ConfigCacheCommand extends Command {
	protected $name = 'config:cache';
	protected $description = 'Create a cache file for faster configuration loading';
	protected $files;
	public function __construct(Filesystem $files)
	{
		parent::__construct();
		$this->files = $files;
	}
	public function fire()
	{
		$this->call('config:clear');
		$config = $this->setRealSessionDriver(
			$this->getFreshConfiguration()
		);
		$this->files->put(
			$this->laravel->getCachedConfigPath(), '<?php return '.var_export($config, true).';'.PHP_EOL
		);
		$this->info('Configuration cached successfully!');
	}
	protected function getFreshConfiguration()
	{
		$app = require $this->laravel->basePath().'/bootstrap/app.php';
		$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
		return $app['config']->all();
	}
	protected function setRealSessionDriver(array $config)
	{
		$session = require $this->laravel->configPath().'/session.php';
		$config['session']['driver'] = $session['driver'];
		return $config;
	}
}
