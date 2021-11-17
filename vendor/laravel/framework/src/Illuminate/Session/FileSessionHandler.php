<?php namespace Illuminate\Session;
use SessionHandlerInterface;
use Symfony\Component\Finder\Finder;
use Illuminate\Filesystem\Filesystem;
class FileSessionHandler implements SessionHandlerInterface {
	protected $files;
	protected $path;
	public function __construct(Filesystem $files, $path)
	{
		$this->path = $path;
		$this->files = $files;
	}
	public function open($savePath, $sessionName)
	{
		return true;
	}
	public function close()
	{
		return true;
	}
	public function read($sessionId)
	{
		if ($this->files->exists($path = $this->path.'/'.$sessionId))
		{
			return $this->files->get($path);
		}
		return '';
	}
	public function write($sessionId, $data)
	{
		$this->files->put($this->path.'/'.$sessionId, $data, true);
	}
	public function destroy($sessionId)
	{
		$this->files->delete($this->path.'/'.$sessionId);
	}
	public function gc($lifetime)
	{
		$files = Finder::create()
					->in($this->path)
					->files()
					->ignoreDotFiles(true)
					->date('<= now - '.$lifetime.' seconds');
		foreach ($files as $file)
		{
			$this->files->delete($file->getRealPath());
		}
	}
}
