<?php namespace Illuminate\Auth\Console;
use Illuminate\Console\Command;
class ClearResetsCommand extends Command {
	protected $name = 'auth:clear-resets';
	protected $description = 'Flush expired password reset tokens';
	public function fire()
	{
		$this->laravel['auth.password.tokens']->deleteExpired();
		$this->info('Expired reset tokens cleared!');
	}
}
