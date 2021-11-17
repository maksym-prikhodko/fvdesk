<?php namespace Illuminate\Database\Eloquent\Relations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
class MorphToMany extends BelongsToMany {
	protected $morphType;
	protected $morphClass;
	protected $inverse;
	public function __construct(Builder $query, Model $parent, $name, $table, $foreignKey, $otherKey, $relationName = null, $inverse = false)
	{
		$this->inverse = $inverse;
		$this->morphType = $name.'_type';
		$this->morphClass = $inverse ? $query->getModel()->getMorphClass() : $parent->getMorphClass();
		parent::__construct($query, $parent, $table, $foreignKey, $otherKey, $relationName);
	}
	protected function setWhere()
	{
		parent::setWhere();
		$this->query->where($this->table.'.'.$this->morphType, $this->morphClass);
		return $this;
	}
	public function getRelationCountQuery(Builder $query, Builder $parent)
	{
		$query = parent::getRelationCountQuery($query, $parent);
		return $query->where($this->table.'.'.$this->morphType, $this->morphClass);
	}
	public function addEagerConstraints(array $models)
	{
		parent::addEagerConstraints($models);
		$this->query->where($this->table.'.'.$this->morphType, $this->morphClass);
	}
	protected function createAttachRecord($id, $timed)
	{
		$record = parent::createAttachRecord($id, $timed);
		return array_add($record, $this->morphType, $this->morphClass);
	}
	protected function newPivotQuery()
	{
		$query = parent::newPivotQuery();
		return $query->where($this->morphType, $this->morphClass);
	}
	public function newPivot(array $attributes = array(), $exists = false)
	{
		$pivot = new MorphPivot($this->parent, $attributes, $this->table, $exists);
		$pivot->setPivotKeys($this->foreignKey, $this->otherKey)
			  ->setMorphType($this->morphType)
			  ->setMorphClass($this->morphClass);
		return $pivot;
	}
	public function getMorphType()
	{
		return $this->morphType;
	}
	public function getMorphClass()
	{
		return $this->morphClass;
	}
}
