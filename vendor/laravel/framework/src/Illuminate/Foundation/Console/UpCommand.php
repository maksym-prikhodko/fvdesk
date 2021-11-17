<?php namespace Illuminate\Foundation\Console;
use Illuminate\Console\Command;
class UpCommand extends Command {
	protected $name = 'up';
	protected $description = "Bring the application out of maintenance mode";
	public function fire()
	{
		@unlink($this->laravel->storagePath().'/framework/down');
		$this->info('Application is now live.');
	}
}
