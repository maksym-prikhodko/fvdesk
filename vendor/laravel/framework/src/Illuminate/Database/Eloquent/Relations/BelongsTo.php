<?php namespace Illuminate\Database\Eloquent\Relations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Expression;
use Illuminate\Database\Eloquent\Collection;
class BelongsTo extends Relation {
	protected $foreignKey;
	protected $otherKey;
	protected $relation;
	public function __construct(Builder $query, Model $parent, $foreignKey, $otherKey, $relation)
	{
		$this->otherKey = $otherKey;
		$this->relation = $relation;
		$this->foreignKey = $foreignKey;
		parent::__construct($query, $parent);
	}
	public function getResults()
	{
		return $this->query->first();
	}
	public function addConstraints()
	{
		if (static::$constraints)
		{
			$table = $this->related->getTable();
			$this->query->where($table.'.'.$this->otherKey, '=', $this->parent->{$this->foreignKey});
		}
	}
	public function getRelationCountQuery(Builder $query, Builder $parent)
	{
		if ($parent->getQuery()->from == $query->getQuery()->from)
		{
			return $this->getRelationCountQueryForSelfRelation($query, $parent);
		}
		$query->select(new Expression('count(*)'));
		$otherKey = $this->wrap($query->getModel()->getTable().'.'.$this->otherKey);
		return $query->where($this->getQualifiedForeignKey(), '=', new Expression($otherKey));
	}
	public function getRelationCountQueryForSelfRelation(Builder $query, Builder $parent)
	{
		$query->select(new Expression('count(*)'));
		$tablePrefix = $this->query->getQuery()->getConnection()->getTablePrefix();
		$query->from($query->getModel()->getTable().' as '.$tablePrefix.$hash = $this->getRelationCountHash());
		$key = $this->wrap($this->getQualifiedForeignKey());
		return $query->where($hash.'.'.$query->getModel()->getKeyName(), '=', new Expression($key));
	}
	public function getRelationCountHash()
	{
		return 'self_'.md5(microtime(true));
	}
	public function addEagerConstraints(array $models)
	{
		$key = $this->related->getTable().'.'.$this->otherKey;
		$this->query->whereIn($key, $this->getEagerModelKeys($models));
	}
	protected function getEagerModelKeys(array $models)
	{
		$keys = array();
		foreach ($models as $model)
		{
			if ( ! is_null($value = $model->{$this->foreignKey}))
			{
				$keys[] = $value;
			}
		}
		if (count($keys) == 0)
		{
			return array(0);
		}
		return array_values(array_unique($keys));
	}
	public function initRelation(array $models, $relation)
	{
		foreach ($models as $model)
		{
			$model->setRelation($relation, null);
		}
		return $models;
	}
	public function match(array $models, Collection $results, $relation)
	{
		$foreign = $this->foreignKey;
		$other = $this->otherKey;
		$dictionary = array();
		foreach ($results as $result)
		{
			$dictionary[$result->getAttribute($other)] = $result;
		}
		foreach ($models as $model)
		{
			if (isset($dictionary[$model->$foreign]))
			{
				$model->setRelation($relation, $dictionary[$model->$foreign]);
			}
		}
		return $models;
	}
	public function associate(Model $model)
	{
		$this->parent->setAttribute($this->foreignKey, $model->getAttribute($this->otherKey));
		return $this->parent->setRelation($this->relation, $model);
	}
	public function dissociate()
	{
		$this->parent->setAttribute($this->foreignKey, null);
		return $this->parent->setRelation($this->relation, null);
	}
	public function update(array $attributes)
	{
		$instance = $this->getResults();
		return $instance->fill($attributes)->save();
	}
	public function getForeignKey()
	{
		return $this->foreignKey;
	}
	public function getQualifiedForeignKey()
	{
		return $this->parent->getTable().'.'.$this->foreignKey;
	}
	public function getOtherKey()
	{
		return $this->otherKey;
	}
	public function getQualifiedOtherKeyName()
	{
		return $this->related->getTable().'.'.$this->otherKey;
	}
}
