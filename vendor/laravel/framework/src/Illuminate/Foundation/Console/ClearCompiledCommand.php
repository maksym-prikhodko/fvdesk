<?php namespace Illuminate\Foundation\Console;
use Illuminate\Console\Command;
class ClearCompiledCommand extends Command {
	protected $name = 'clear-compiled';
	protected $description = "Remove the compiled class file";
	public function fire()
	{
		$compiledPath = $this->laravel->getCachedCompilePath();
		$servicesPath = $this->laravel->getCachedServicesPath();
		if (file_exists($compiledPath))
		{
			@unlink($compiledPath);
		}
		if (file_exists($servicesPath))
		{
			@unlink($servicesPath);
		}
	}
}
