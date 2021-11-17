<?php namespace Illuminate\Queue\Console;
use Illuminate\Console\Command;
class ListFailedCommand extends Command {
	protected $name = 'queue:failed';
	protected $description = 'List all of the failed queue jobs';
	public function fire()
	{
		$rows = array();
		foreach ($this->laravel['queue.failer']->all() as $failed)
		{
			$rows[] = $this->parseFailedJob((array) $failed);
		}
		if (count($rows) == 0)
		{
			return $this->info('No failed jobs!');
		}
		$table = $this->getHelperSet()->get('table');
		$table->setHeaders(array('ID', 'Connection', 'Queue', 'Class', 'Failed At'))
              ->setRows($rows)
              ->render($this->output);
	}
	protected function parseFailedJob(array $failed)
	{
		$row = array_values(array_except($failed, array('payload')));
		array_splice($row, 3, 0, array_get(json_decode($failed['payload'], true), 'job'));
		return $row;
	}
}
