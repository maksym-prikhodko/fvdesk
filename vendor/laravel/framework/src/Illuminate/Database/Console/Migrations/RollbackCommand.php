<?php namespace Illuminate\Database\Console\Migrations;
use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use Illuminate\Database\Migrations\Migrator;
use Symfony\Component\Console\Input\InputOption;
class RollbackCommand extends Command {
	use ConfirmableTrait;
	protected $name = 'migrate:rollback';
	protected $description = 'Rollback the last database migration';
	protected $migrator;
	public function __construct(Migrator $migrator)
	{
		parent::__construct();
		$this->migrator = $migrator;
	}
	public function fire()
	{
		if ( ! $this->confirmToProceed()) return;
		$this->migrator->setConnection($this->input->getOption('database'));
		$pretend = $this->input->getOption('pretend');
		$this->migrator->rollback($pretend);
		foreach ($this->migrator->getNotes() as $note)
		{
			$this->output->writeln($note);
		}
	}
	protected function getOptions()
	{
		return array(
			array('database', null, InputOption::VALUE_OPTIONAL, 'The database connection to use.'),
			array('force', null, InputOption::VALUE_NONE, 'Force the operation to run when in production.'),
			array('pretend', null, InputOption::VALUE_NONE, 'Dump the SQL queries that would be run.'),
		);
	}
}
