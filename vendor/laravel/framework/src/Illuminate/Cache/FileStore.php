<?php namespace Illuminate\Cache;
use Exception;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Contracts\Cache\Store;
class FileStore implements Store {
	protected $files;
	protected $directory;
	public function __construct(Filesystem $files, $directory)
	{
		$this->files = $files;
		$this->directory = $directory;
	}
	public function get($key)
	{
		return array_get($this->getPayload($key), 'data');
	}
	protected function getPayload($key)
	{
		$path = $this->path($key);
		try
		{
			$expire = substr($contents = $this->files->get($path), 0, 10);
		}
		catch (Exception $e)
		{
			return array('data' => null, 'time' => null);
		}
		if (time() >= $expire)
		{
			$this->forget($key);
			return array('data' => null, 'time' => null);
		}
		$data = unserialize(substr($contents, 10));
		$time = ceil(($expire - time()) / 60);
		return compact('data', 'time');
	}
	public function put($key, $value, $minutes)
	{
		$value = $this->expiration($minutes).serialize($value);
		$this->createCacheDirectory($path = $this->path($key));
		$this->files->put($path, $value);
	}
	protected function createCacheDirectory($path)
	{
		try
		{
			$this->files->makeDirectory(dirname($path), 0777, true, true);
		}
		catch (Exception $e)
		{
		}
	}
	public function increment($key, $value = 1)
	{
		$raw = $this->getPayload($key);
		$int = ((int) $raw['data']) + $value;
		$this->put($key, $int, (int) $raw['time']);
		return $int;
	}
	public function decrement($key, $value = 1)
	{
		return $this->increment($key, $value * -1);
	}
	public function forever($key, $value)
	{
		$this->put($key, $value, 0);
	}
	public function forget($key)
	{
		$file = $this->path($key);
		if ($this->files->exists($file))
		{
			return $this->files->delete($file);
		}
		return false;
	}
	public function flush()
	{
		if ($this->files->isDirectory($this->directory))
		{
			foreach ($this->files->directories($this->directory) as $directory)
			{
				$this->files->deleteDirectory($directory);
			}
		}
	}
	protected function path($key)
	{
		$parts = array_slice(str_split($hash = md5($key), 2), 0, 2);
		return $this->directory.'/'.implode('/', $parts).'/'.$hash;
	}
	protected function expiration($minutes)
	{
		if ($minutes === 0) return 9999999999;
		return time() + ($minutes * 60);
	}
	public function getFilesystem()
	{
		return $this->files;
	}
	public function getDirectory()
	{
		return $this->directory;
	}
	public function getPrefix()
	{
		return '';
	}
}
