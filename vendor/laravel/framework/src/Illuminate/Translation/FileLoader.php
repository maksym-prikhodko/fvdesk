<?php namespace Illuminate\Translation;
use Illuminate\Filesystem\Filesystem;
class FileLoader implements LoaderInterface {
	protected $files;
	protected $path;
	protected $hints = array();
	public function __construct(Filesystem $files, $path)
	{
		$this->path = $path;
		$this->files = $files;
	}
	public function load($locale, $group, $namespace = null)
	{
		if (is_null($namespace) || $namespace == '*')
		{
			return $this->loadPath($this->path, $locale, $group);
		}
		return $this->loadNamespaced($locale, $group, $namespace);
	}
	protected function loadNamespaced($locale, $group, $namespace)
	{
		if (isset($this->hints[$namespace]))
		{
			$lines = $this->loadPath($this->hints[$namespace], $locale, $group);
			return $this->loadNamespaceOverrides($lines, $locale, $group, $namespace);
		}
		return array();
	}
	protected function loadNamespaceOverrides(array $lines, $locale, $group, $namespace)
	{
		$file = "{$this->path}/packages/{$locale}/{$namespace}/{$group}.php";
		if ($this->files->exists($file))
		{
			return array_replace_recursive($lines, $this->files->getRequire($file));
		}
		return $lines;
	}
	protected function loadPath($path, $locale, $group)
	{
		if ($this->files->exists($full = "{$path}/{$locale}/{$group}.php"))
		{
			return $this->files->getRequire($full);
		}
		return array();
	}
	public function addNamespace($namespace, $hint)
	{
		$this->hints[$namespace] = $hint;
	}
}
