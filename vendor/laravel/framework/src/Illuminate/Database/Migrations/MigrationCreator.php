<?php namespace Illuminate\Database\Migrations;
use Closure;
use Illuminate\Filesystem\Filesystem;
class MigrationCreator {
	protected $files;
	protected $postCreate = array();
	public function __construct(Filesystem $files)
	{
		$this->files = $files;
	}
	public function create($name, $path, $table = null, $create = false)
	{
		$path = $this->getPath($name, $path);
		$stub = $this->getStub($table, $create);
		$this->files->put($path, $this->populateStub($name, $stub, $table));
		$this->firePostCreateHooks();
		return $path;
	}
	protected function getStub($table, $create)
	{
		if (is_null($table))
		{
			return $this->files->get($this->getStubPath().'/blank.stub');
		}
		else
		{
			$stub = $create ? 'create.stub' : 'update.stub';
			return $this->files->get($this->getStubPath()."/{$stub}");
		}
	}
	protected function populateStub($name, $stub, $table)
	{
		$stub = str_replace('{{class}}', $this->getClassName($name), $stub);
		if ( ! is_null($table))
		{
			$stub = str_replace('{{table}}', $table, $stub);
		}
		return $stub;
	}
	protected function getClassName($name)
	{
		return studly_case($name);
	}
	protected function firePostCreateHooks()
	{
		foreach ($this->postCreate as $callback)
		{
			call_user_func($callback);
		}
	}
	public function afterCreate(Closure $callback)
	{
		$this->postCreate[] = $callback;
	}
	protected function getPath($name, $path)
	{
		return $path.'/'.$this->getDatePrefix().'_'.$name.'.php';
	}
	protected function getDatePrefix()
	{
		return date('Y_m_d_His');
	}
	public function getStubPath()
	{
		return __DIR__.'/stubs';
	}
	public function getFilesystem()
	{
		return $this->files;
	}
}
