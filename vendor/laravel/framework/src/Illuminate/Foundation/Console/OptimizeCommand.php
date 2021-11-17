<?php namespace Illuminate\Foundation\Console;
use Illuminate\Console\Command;
use Illuminate\Foundation\Composer;
use ClassPreloader\Command\PreCompileCommand;
use Symfony\Component\Console\Input\InputOption;
class OptimizeCommand extends Command {
	protected $name = 'optimize';
	protected $description = "Optimize the framework for better performance";
	protected $composer;
	public function __construct(Composer $composer)
	{
		parent::__construct();
		$this->composer = $composer;
	}
	public function fire()
	{
		$this->info('Generating optimized class loader');
		if ($this->option('psr'))
		{
			$this->composer->dumpAutoloads();
		}
		else
		{
			$this->composer->dumpOptimized();
		}
		if ($this->option('force') || ! $this->laravel['config']['app.debug'])
		{
			$this->info('Compiling common classes');
			$this->compileClasses();
		}
		else
		{
			$this->call('clear-compiled');
		}
	}
	protected function compileClasses()
	{
		$this->registerClassPreloaderCommand();
		$this->callSilent('compile', array(
			'--config' => implode(',', $this->getClassFiles()),
			'--output' => $this->laravel->getCachedCompilePath(),
			'--strip_comments' => 1,
		));
	}
	protected function getClassFiles()
	{
		$app = $this->laravel;
		$core = require __DIR__.'/Optimize/config.php';
		$files = array_merge($core, $this->laravel['config']->get('compile.files', []));
		foreach ($this->laravel['config']->get('compile.providers', []) as $provider)
		{
			$files = array_merge($files, forward_static_call([$provider, 'compiles']));
		}
		return $files;
	}
	protected function registerClassPreloaderCommand()
	{
		$this->getApplication()->add(new PreCompileCommand);
	}
	protected function getOptions()
	{
		return array(
			array('force', null, InputOption::VALUE_NONE, 'Force the compiled class file to be written.'),
			array('psr', null, InputOption::VALUE_NONE, 'Do not optimize Composer dump-autoload.'),
		);
	}
}
