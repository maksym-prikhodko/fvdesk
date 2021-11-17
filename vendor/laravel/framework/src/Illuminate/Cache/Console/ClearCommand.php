<?php namespace Illuminate\Cache\Console;
use Illuminate\Console\Command;
use Illuminate\Cache\CacheManager;
use Symfony\Component\Console\Input\InputArgument;
class ClearCommand extends Command {
	protected $name = 'cache:clear';
	protected $description = "Flush the application cache";
	protected $cache;
	public function __construct(CacheManager $cache)
	{
		parent::__construct();
		$this->cache = $cache;
	}
	public function fire()
	{
		$storeName = $this->argument('store');
		$this->laravel['events']->fire('cache:clearing', [$storeName]);
		$this->cache->store($storeName)->flush();
		$this->laravel['events']->fire('cache:cleared', [$storeName]);
		$this->info('Application cache cleared!');
	}
	protected function getArguments()
	{
		return [
			['store', InputArgument::OPTIONAL, 'The name of the store you would like to clear.'],
		];
	}
}
