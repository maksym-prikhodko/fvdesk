<?php namespace Illuminate\Queue\Console;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
class ForgetFailedCommand extends Command {
	protected $name = 'queue:forget';
	protected $description = 'Delete a failed queue job';
	public function fire()
	{
		if ($this->laravel['queue.failer']->forget($this->argument('id')))
		{
			$this->info('Failed job deleted successfully!');
		}
		else
		{
			$this->error('No failed job matches the given ID.');
		}
	}
	protected function getArguments()
	{
		return array(
			array('id', InputArgument::REQUIRED, 'The ID of the failed job'),
		);
	}
}
