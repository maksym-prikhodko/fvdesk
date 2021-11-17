<?php namespace Illuminate\Queue\Console;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
class RetryCommand extends Command {
	protected $name = 'queue:retry';
	protected $description = 'Retry a failed queue job';
	public function fire()
	{
		$failed = $this->laravel['queue.failer']->find($this->argument('id'));
		if ( ! is_null($failed))
		{
			$failed->payload = $this->resetAttempts($failed->payload);
			$this->laravel['queue']->connection($failed->connection)->pushRaw($failed->payload, $failed->queue);
			$this->laravel['queue.failer']->forget($failed->id);
			$this->info('The failed job has been pushed back onto the queue!');
		}
		else
		{
			$this->error('No failed job matches the given ID.');
		}
	}
	protected function resetAttempts($payload)
	{
		$payload = json_decode($payload, true);
		if (isset($payload['attempts'])) $payload['attempts'] = 0;
		return json_encode($payload);
	}
	protected function getArguments()
	{
		return array(
			array('id', InputArgument::REQUIRED, 'The ID of the failed job'),
		);
	}
}
