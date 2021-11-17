<?php namespace Illuminate\Queue\Console;
use Illuminate\Console\Command;
class RestartCommand extends Command {
	protected $name = 'queue:restart';
	protected $description = "Restart queue worker daemons after their current job";
	public function fire()
	{
		$this->laravel['cache']->forever('illuminate:queue:restart', time());
		$this->info('Broadcasting queue restart signal.');
	}
}
