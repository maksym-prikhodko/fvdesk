<?php namespace Illuminate\View\Compilers;
use Illuminate\Filesystem\Filesystem;
abstract class Compiler {
	protected $files;
	protected $cachePath;
	public function __construct(Filesystem $files, $cachePath)
	{
		$this->files = $files;
		$this->cachePath = $cachePath;
	}
	public function getCompiledPath($path)
	{
		return $this->cachePath.'/'.md5($path);
	}
	public function isExpired($path)
	{
		$compiled = $this->getCompiledPath($path);
		if ( ! $this->cachePath || ! $this->files->exists($compiled))
		{
			return true;
		}
		$lastModified = $this->files->lastModified($path);
		return $lastModified >= $this->files->lastModified($compiled);
	}
}
