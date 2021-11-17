<?php namespace Illuminate\Foundation\Console;
use Illuminate\Console\Command;
use Illuminate\Foundation\Composer;
use Symfony\Component\Finder\Finder;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Console\AppNamespaceDetectorTrait;
use Symfony\Component\Console\Input\InputArgument;
class AppNameCommand extends Command {
	use AppNamespaceDetectorTrait;
	protected $name = 'app:name';
	protected $description = "Set the application namespace";
	protected $composer;
	protected $files;
	protected $currentRoot;
	public function __construct(Composer $composer, Filesystem $files)
	{
		parent::__construct();
		$this->files = $files;
		$this->composer = $composer;
	}
	public function fire()
	{
		$this->currentRoot = trim($this->getAppNamespace(), '\\');
		$this->setBootstrapNamespaces();
		$this->setAppDirectoryNamespace();
		$this->setConfigNamespaces();
		$this->setComposerNamespace();
		$this->setPhpSpecNamespace();
		$this->info('Application namespace set!');
		$this->composer->dumpAutoloads();
		$this->call('clear-compiled');
	}
	protected function setAppDirectoryNamespace()
	{
		$files = Finder::create()
                            ->in($this->laravel['path'])
                            ->name('*.php');
		foreach ($files as $file)
		{
			$this->replaceNamespace($file->getRealPath());
		}
	}
	protected function replaceNamespace($path)
	{
		$search = [
			'namespace '.$this->currentRoot.';',
			$this->currentRoot.'\\',
		];
		$replace = [
			'namespace '.$this->argument('name').';',
			$this->argument('name').'\\',
		];
		$this->replaceIn($path, $search, $replace);
	}
	protected function setBootstrapNamespaces()
	{
		$search = [
			$this->currentRoot.'\\Http',
			$this->currentRoot.'\\Console',
			$this->currentRoot.'\\Exceptions',
		];
		$replace = [
			$this->argument('name').'\\Http',
			$this->argument('name').'\\Console',
			$this->argument('name').'\\Exceptions',
		];
		$this->replaceIn($this->getBootstrapPath(), $search, $replace);
	}
	protected function setComposerNamespace()
	{
		$this->replaceIn(
			$this->getComposerPath(), $this->currentRoot.'\\\\', str_replace('\\', '\\\\', $this->argument('name')).'\\\\'
		);
	}
	protected function setConfigNamespaces()
	{
		$this->setAppConfigNamespaces();
		$this->setAuthConfigNamespace();
		$this->setServicesConfigNamespace();
	}
	protected function setAppConfigNamespaces()
	{
		$search = [
			$this->currentRoot.'\\Providers',
			$this->currentRoot.'\\Http\\Controllers\\',
		];
		$replace = [
			$this->argument('name').'\\Providers',
			$this->argument('name').'\\Http\\Controllers\\',
		];
		$this->replaceIn($this->getConfigPath('app'), $search, $replace);
	}
	protected function setAuthConfigNamespace()
	{
		$this->replaceIn(
			$this->getAuthConfigPath(), $this->currentRoot.'\\User', $this->argument('name').'\\User'
		);
	}
	protected function setServicesConfigNamespace()
	{
		$this->replaceIn(
			$this->getServicesConfigPath(), $this->currentRoot.'\\User', $this->argument('name').'\\User'
		);
	}
	protected function setPhpSpecNamespace()
	{
		if ($this->files->exists($path = $this->getPhpSpecConfigPath()))
		{
			$this->replaceIn($path, $this->currentRoot, $this->argument('name'));
		}
	}
	protected function replaceIn($path, $search, $replace)
	{
		$this->files->put($path, str_replace($search, $replace, $this->files->get($path)));
	}
	protected function getUserClassPath()
	{
		return $this->laravel['path'].'/Core/User.php';
	}
	protected function getBootstrapPath()
	{
		return $this->laravel->basePath().'/bootstrap/app.php';
	}
	protected function getComposerPath()
	{
		return $this->laravel->basePath().'/composer.json';
	}
	protected function getConfigPath($name)
	{
		return $this->laravel['path.config'].'/'.$name.'.php';
	}
	protected function getAuthConfigPath()
	{
		return $this->getConfigPath('auth');
	}
	protected function getServicesConfigPath()
	{
		return $this->getConfigPath('services');
	}
	protected function getPhpSpecConfigPath()
	{
		return $this->laravel->basePath().'/phpspec.yml';
	}
	protected function getArguments()
	{
		return array(
			array('name', InputArgument::REQUIRED, 'The desired namespace.'),
		);
	}
}
