<?php namespace Illuminate\Console;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Console\Input\InputArgument;
abstract class GeneratorCommand extends Command {
	use AppNamespaceDetectorTrait;
	protected $files;
	protected $type;
	public function __construct(Filesystem $files)
	{
		parent::__construct();
		$this->files = $files;
	}
	abstract protected function getStub();
	public function fire()
	{
		$name = $this->parseName($this->getNameInput());
		if ($this->files->exists($path = $this->getPath($name)))
		{
			return $this->error($this->type.' already exists!');
		}
		$this->makeDirectory($path);
		$this->files->put($path, $this->buildClass($name));
		$this->info($this->type.' created successfully.');
	}
	protected function getPath($name)
	{
		$name = str_replace($this->getAppNamespace(), '', $name);
		return $this->laravel['path'].'/'.str_replace('\\', '/', $name).'.php';
	}
	protected function parseName($name)
	{
		$rootNamespace = $this->getAppNamespace();
		if (starts_with($name, $rootNamespace))
		{
			return $name;
		}
		if (str_contains($name, '/'))
		{
			$name = str_replace('/', '\\', $name);
		}
		return $this->parseName($this->getDefaultNamespace(trim($rootNamespace, '\\')).'\\'.$name);
	}
	protected function getDefaultNamespace($rootNamespace)
	{
		return $rootNamespace;
	}
	protected function makeDirectory($path)
	{
		if ( ! $this->files->isDirectory(dirname($path)))
		{
			$this->files->makeDirectory(dirname($path), 0777, true, true);
		}
	}
	protected function buildClass($name)
	{
		$stub = $this->files->get($this->getStub());
		return $this->replaceNamespace($stub, $name)->replaceClass($stub, $name);
	}
	protected function replaceNamespace(&$stub, $name)
	{
		$stub = str_replace(
			'{{namespace}}', $this->getNamespace($name), $stub
		);
		$stub = str_replace(
			'{{rootNamespace}}', $this->getAppNamespace(), $stub
		);
		return $this;
	}
	protected function getNamespace($name)
	{
		return trim(implode('\\', array_slice(explode('\\', $name), 0, -1)), '\\');
	}
	protected function replaceClass($stub, $name)
	{
		$class = str_replace($this->getNamespace($name).'\\', '', $name);
		return str_replace('{{class}}', $class, $stub);
	}
	protected function getNameInput()
	{
		return $this->argument('name');
	}
	protected function getArguments()
	{
		return array(
			array('name', InputArgument::REQUIRED, 'The name of the class'),
		);
	}
}
