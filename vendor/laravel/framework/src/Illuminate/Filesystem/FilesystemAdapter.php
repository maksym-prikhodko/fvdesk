<?php namespace Illuminate\Filesystem;
use InvalidArgumentException;
use Illuminate\Support\Collection;
use League\Flysystem\AdapterInterface;
use League\Flysystem\FilesystemInterface;
use League\Flysystem\FileNotFoundException;
use Illuminate\Contracts\Filesystem\Filesystem as FilesystemContract;
use Illuminate\Contracts\Filesystem\Cloud as CloudFilesystemContract;
use Illuminate\Contracts\Filesystem\FileNotFoundException as ContractFileNotFoundException;
class FilesystemAdapter implements FilesystemContract, CloudFilesystemContract {
	protected $driver;
	public function __construct(FilesystemInterface $driver)
	{
		$this->driver = $driver;
	}
	public function exists($path)
	{
		return $this->driver->has($path);
	}
	public function get($path)
	{
		try
		{
			return $this->driver->read($path);
		}
		catch (FileNotFoundException $e)
		{
			throw new ContractFileNotFoundException($path, $e->getCode(), $e);
		}
	}
	public function put($path, $contents, $visibility = null)
	{
		return $this->driver->put($path, $contents, ['visibility' => $this->parseVisibility($visibility)]);
	}
	public function getVisibility($path)
	{
		if ($this->driver->getVisibility($path) == AdapterInterface::VISIBILITY_PUBLIC)
		{
			return FilesystemContract::VISIBILITY_PUBLIC;
		}
		return FilesystemContract::VISIBILITY_PRIVATE;
	}
	public function setVisibility($path, $visibility)
	{
		return $this->driver->setVisibility($path, $this->parseVisibility($visibility));
	}
	public function prepend($path, $data)
	{
		return $this->put($path, $data.PHP_EOL.$this->get($path));
	}
	public function append($path, $data)
	{
		return $this->put($path, $this->get($path).PHP_EOL.$data);
	}
	public function delete($paths)
	{
		$paths = is_array($paths) ? $paths : func_get_args();
		foreach ($paths as $path)
		{
			$this->driver->delete($path);
		}
		return true;
	}
	public function copy($from, $to)
	{
		return $this->driver->copy($from, $to);
	}
	public function move($from, $to)
	{
		$this->driver->rename($from, $to);
	}
	public function size($path)
	{
		return $this->driver->getSize($path);
	}
	public function mimeType($path)
	{
		return $this->driver->getMimetype($path);
	}
	public function lastModified($path)
	{
		return $this->driver->getTimestamp($path);
	}
	public function files($directory = null, $recursive = false)
	{
		$contents = $this->driver->listContents($directory, $recursive);
		return $this->filterContentsByType($contents, 'file');
	}
	public function allFiles($directory = null)
	{
		return $this->files($directory, true);
	}
	public function directories($directory = null, $recursive = false)
	{
		$contents = $this->driver->listContents($directory, $recursive);
		return $this->filterContentsByType($contents, 'dir');
	}
	public function allDirectories($directory = null, $recursive = false)
	{
		return $this->directories($directory, true);
	}
	public function makeDirectory($path)
	{
		return $this->driver->createDir($path);
	}
	public function deleteDirectory($directory)
	{
		return $this->driver->deleteDir($directory);
	}
	public function getDriver()
	{
		return $this->driver;
	}
	protected function filterContentsByType($contents, $type)
	{
		return Collection::make($contents)
			->where('type', $type)
			->fetch('path')
			->values()->all();
	}
	protected function parseVisibility($visibility)
	{
		if (is_null($visibility)) return;
		switch ($visibility)
		{
			case FilesystemContract::VISIBILITY_PUBLIC:
				return AdapterInterface::VISIBILITY_PUBLIC;
			case FilesystemContract::VISIBILITY_PRIVATE:
				return AdapterInterface::VISIBILITY_PRIVATE;
		}
		throw new InvalidArgumentException('Unknown visibility: '.$visibility);
	}
}
