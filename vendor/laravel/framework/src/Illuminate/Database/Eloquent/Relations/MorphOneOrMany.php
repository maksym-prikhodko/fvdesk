<?php namespace Illuminate\Database\Eloquent\Relations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
abstract class MorphOneOrMany extends HasOneOrMany {
	protected $morphType;
	protected $morphClass;
	public function __construct(Builder $query, Model $parent, $type, $id, $localKey)
	{
		$this->morphType = $type;
		$this->morphClass = $parent->getMorphClass();
		parent::__construct($query, $parent, $id, $localKey);
	}
	public function addConstraints()
	{
		if (static::$constraints)
		{
			parent::addConstraints();
			$this->query->where($this->morphType, $this->morphClass);
		}
	}
	public function getRelationCountQuery(Builder $query, Builder $parent)
	{
		$query = parent::getRelationCountQuery($query, $parent);
		return $query->where($this->morphType, $this->morphClass);
	}
	public function addEagerConstraints(array $models)
	{
		parent::addEagerConstraints($models);
		$this->query->where($this->morphType, $this->morphClass);
	}
	public function save(Model $model)
	{
		$model->setAttribute($this->getPlainMorphType(), $this->morphClass);
		return parent::save($model);
	}
	public function findOrNew($id, $columns = ['*'])
	{
		if (is_null($instance = $this->find($id, $columns)))
		{
			$instance = $this->related->newInstance();
			$this->setForeignAttributesForCreate($instance);
		}
		return $instance;
	}
	public function firstOrNew(array $attributes)
	{
		if (is_null($instance = $this->where($attributes)->first()))
		{
			$instance = $this->related->newInstance();
			$this->setForeignAttributesForCreate($instance);
		}
		return $instance;
	}
	public function firstOrCreate(array $attributes)
	{
		if (is_null($instance = $this->where($attributes)->first()))
		{
			$instance = $this->create($attributes);
		}
		return $instance;
	}
	public function updateOrCreate(array $attributes, array $values = [])
	{
		$instance = $this->firstOrNew($attributes);
		$instance->fill($values);
		$instance->save();
		return $instance;
	}
	public function create(array $attributes)
	{
		$instance = $this->related->newInstance($attributes);
		$this->setForeignAttributesForCreate($instance);
		$instance->save();
		return $instance;
	}
	protected function setForeignAttributesForCreate(Model $model)
	{
		$model->{$this->getPlainForeignKey()} = $this->getParentKey();
		$model->{last(explode('.', $this->morphType))} = $this->morphClass;
	}
	public function getMorphType()
	{
		return $this->morphType;
	}
	public function getPlainMorphType()
	{
		return last(explode('.', $this->morphType));
	}
	public function getMorphClass()
	{
		return $this->morphClass;
	}
}
