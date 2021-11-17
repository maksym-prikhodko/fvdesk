<?php namespace Illuminate\Foundation\Console;
use Illuminate\Console\Command;
class DownCommand extends Command {
	protected $name = 'down';
	protected $description = "Put the application into maintenance mode";
	public function fire()
	{
		touch($this->laravel->storagePath().'/framework/down');
		$this->comment('Application is now in maintenance mode.');
	}
}
