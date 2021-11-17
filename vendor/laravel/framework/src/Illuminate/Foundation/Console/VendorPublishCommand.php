<?php namespace Illuminate\Foundation\Console;
use Illuminate\Console\Command;
use League\Flysystem\MountManager;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\ServiceProvider;
use League\Flysystem\Filesystem as Flysystem;
use Symfony\Component\Console\Input\InputOption;
use League\Flysystem\Adapter\Local as LocalAdapter;
class VendorPublishCommand extends Command {
	protected $files;
	protected $name = 'vendor:publish';
	protected $description = "Publish any publishable assets from vendor packages";
	public function __construct(Filesystem $files)
	{
		parent::__construct();
		$this->files = $files;
	}
	public function fire()
	{
		$paths = ServiceProvider::pathsToPublish(
			$this->option('provider'), $this->option('tag')
		);
		if (empty($paths))
		{
			return $this->comment("Nothing to publish.");
		}
		foreach ($paths as $from => $to)
		{
			if ($this->files->isFile($from))
			{
				$this->publishFile($from, $to);
			}
			elseif ($this->files->isDirectory($from))
			{
				$this->publishDirectory($from, $to);
			}
			else
			{
				$this->error("Can't locate path: <{$from}>");
			}
		}
		$this->info('Publishing Complete!');
	}
	protected function publishFile($from, $to)
	{
		if ($this->files->exists($to) && ! $this->option('force'))
		{
			return;
		}
		$this->createParentDirectory(dirname($to));
		$this->files->copy($from, $to);
		$this->status($from, $to, 'File');
	}
	protected function publishDirectory($from, $to)
	{
		$manager = new MountManager([
			'from' => new Flysystem(new LocalAdapter($from)),
			'to' => new Flysystem(new LocalAdapter($to)),
		]);
		foreach ($manager->listContents('from:
		{
			if ($file['type'] === 'file' && ( ! $manager->has('to:
			{
				$manager->put('to:
			}
		}
		$this->status($from, $to, 'Directory');
	}
	protected function createParentDirectory($directory)
	{
		if ( ! $this->files->isDirectory($directory))
		{
			$this->files->makeDirectory($directory, 0755, true);
		}
	}
	protected function status($from, $to, $type)
	{
		$from = str_replace(base_path(), '', realpath($from));
		$to = str_replace(base_path(), '', realpath($to));
		$this->line('<info>Copied '.$type.'</info> <comment>['.$from.']</comment> <info>To</info> <comment>['.$to.']</comment>');
	}
	protected function getOptions()
	{
		return array(
			array('force', null, InputOption::VALUE_NONE, 'Overwrite any existing files.'),
			array('provider', null, InputOption::VALUE_OPTIONAL, 'The service provider that has assets you want to publish.'),
			array('tag', null, InputOption::VALUE_OPTIONAL, 'The tag that has assets you want to publish.'),
		);
	}
}
