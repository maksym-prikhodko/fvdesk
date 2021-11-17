<?php namespace Illuminate\Foundation\Console;
use Psy\Shell;
use Psy\Configuration;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Illuminate\Foundation\Console\Tinker\Presenters\EloquentModelPresenter;
use Illuminate\Foundation\Console\Tinker\Presenters\IlluminateCollectionPresenter;
use Illuminate\Foundation\Console\Tinker\Presenters\IlluminateApplicationPresenter;
class TinkerCommand extends Command {
	protected $commandWhitelist = [
		'clear-compiled', 'down', 'env', 'inspire', 'migrate', 'optimize', 'up',
	];
	protected $name = 'tinker';
	protected $description = "Interact with your application";
	public function fire()
	{
		$this->getApplication()->setCatchExceptions(false);
		$config = new Configuration;
		$config->getPresenterManager()->addPresenters(
			$this->getPresenters()
		);
		$shell = new Shell($config);
		$shell->addCommands($this->getCommands());
		$shell->setIncludes($this->argument('include'));
		$shell->run();
	}
	protected function getCommands()
	{
		$commands = [];
		foreach ($this->getApplication()->all() as $name => $command)
		{
			if (in_array($name, $this->commandWhitelist)) $commands[] = $command;
		}
		return $commands;
	}
	protected function getPresenters()
	{
		return [
			new EloquentModelPresenter,
			new IlluminateCollectionPresenter,
			new IlluminateApplicationPresenter,
		];
	}
	protected function getArguments()
	{
		return [
			['include', InputArgument::IS_ARRAY, 'Include file(s) before starting tinker'],
		];
	}
}
