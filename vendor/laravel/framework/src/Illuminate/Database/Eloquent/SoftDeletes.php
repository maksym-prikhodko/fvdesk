<?php namespace Illuminate\Database\Eloquent;
trait SoftDeletes {
	protected $forceDeleting = false;
	public static function bootSoftDeletes()
	{
		static::addGlobalScope(new SoftDeletingScope);
	}
	public function forceDelete()
	{
		$this->forceDeleting = true;
		$this->delete();
		$this->forceDeleting = false;
	}
	protected function performDeleteOnModel()
	{
		if ($this->forceDeleting)
		{
			return $this->withTrashed()->where($this->getKeyName(), $this->getKey())->forceDelete();
		}
		return $this->runSoftDelete();
	}
	protected function runSoftDelete()
	{
		$query = $this->newQuery()->where($this->getKeyName(), $this->getKey());
		$this->{$this->getDeletedAtColumn()} = $time = $this->freshTimestamp();
		$query->update(array($this->getDeletedAtColumn() => $this->fromDateTime($time)));
	}
	public function restore()
	{
		if ($this->fireModelEvent('restoring') === false)
		{
			return false;
		}
		$this->{$this->getDeletedAtColumn()} = null;
		$this->exists = true;
		$result = $this->save();
		$this->fireModelEvent('restored', false);
		return $result;
	}
	public function trashed()
	{
		return ! is_null($this->{$this->getDeletedAtColumn()});
	}
	public static function withTrashed()
	{
		return (new static)->newQueryWithoutScope(new SoftDeletingScope);
	}
	public static function onlyTrashed()
	{
		$instance = new static;
		$column = $instance->getQualifiedDeletedAtColumn();
		return $instance->newQueryWithoutScope(new SoftDeletingScope)->whereNotNull($column);
	}
	public static function restoring($callback)
	{
		static::registerModelEvent('restoring', $callback);
	}
	public static function restored($callback)
	{
		static::registerModelEvent('restored', $callback);
	}
	public function getDeletedAtColumn()
	{
		return defined('static::DELETED_AT') ? static::DELETED_AT : 'deleted_at';
	}
	public function getQualifiedDeletedAtColumn()
	{
		return $this->getTable().'.'.$this->getDeletedAtColumn();
	}
}
