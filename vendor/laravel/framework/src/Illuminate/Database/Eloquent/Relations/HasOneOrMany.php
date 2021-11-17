<?php namespace Illuminate\Database\Eloquent\Relations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Expression;
use Illuminate\Database\Eloquent\Collection;
abstract class HasOneOrMany extends Relation {
	protected $foreignKey;
	protected $localKey;
	public function __construct(Builder $query, Model $parent, $foreignKey, $localKey)
	{
		$this->localKey = $localKey;
		$this->foreignKey = $foreignKey;
		parent::__construct($query, $parent);
	}
	public function addConstraints()
	{
		if (static::$constraints)
		{
			$this->query->where($this->foreignKey, '=', $this->getParentKey());
			$this->query->whereNotNull($this->foreignKey);
		}
	}
	public function getRelationCountQuery(Builder $query, Builder $parent)
	{
		if ($parent->getQuery()->from == $query->getQuery()->from)
		{
			return $this->getRelationCountQueryForSelfRelation($query, $parent);
		}
		return parent::getRelationCountQuery($query, $parent);
	}
	public function getRelationCountQueryForSelfRelation(Builder $query, Builder $parent)
	{
		$query->select(new Expression('count(*)'));
		$tablePrefix = $this->query->getQuery()->getConnection()->getTablePrefix();
		$query->from($query->getModel()->getTable().' as '.$tablePrefix.$hash = $this->getRelationCountHash());
		$key = $this->wrap($this->getQualifiedParentKeyName());
		return $query->where($hash.'.'.$this->getPlainForeignKey(), '=', new Expression($key));
	}
	public function getRelationCountHash()
	{
		return 'self_'.md5(microtime(true));
	}
	public function addEagerConstraints(array $models)
	{
		$this->query->whereIn($this->foreignKey, $this->getKeys($models, $this->localKey));
	}
	public function matchOne(array $models, Collection $results, $relation)
	{
		return $this->matchOneOrMany($models, $results, $relation, 'one');
	}
	public function matchMany(array $models, Collection $results, $relation)
	{
		return $this->matchOneOrMany($models, $results, $relation, 'many');
	}
	protected function matchOneOrMany(array $models, Collection $results, $relation, $type)
	{
		$dictionary = $this->buildDictionary($results);
		foreach ($models as $model)
		{
			$key = $model->getAttribute($this->localKey);
			if (isset($dictionary[$key]))
			{
				$value = $this->getRelationValue($dictionary, $key, $type);
				$model->setRelation($relation, $value);
			}
		}
		return $models;
	}
	protected function getRelationValue(array $dictionary, $key, $type)
	{
		$value = $dictionary[$key];
		return $type == 'one' ? reset($value) : $this->related->newCollection($value);
	}
	protected function buildDictionary(Collection $results)
	{
		$dictionary = array();
		$foreign = $this->getPlainForeignKey();
		foreach ($results as $result)
		{
			$dictionary[$result->{$foreign}][] = $result;
		}
		return $dictionary;
	}
	public function save(Model $model)
	{
		$model->setAttribute($this->getPlainForeignKey(), $this->getParentKey());
		return $model->save() ? $model : false;
	}
	public function saveMany(array $models)
	{
		array_walk($models, array($this, 'save'));
		return $models;
	}
	public function findOrNew($id, $columns = ['*'])
	{
		if (is_null($instance = $this->find($id, $columns)))
		{
			$instance = $this->related->newInstance();
			$instance->setAttribute($this->getPlainForeignKey(), $this->getParentKey());
		}
		return $instance;
	}
	public function firstOrNew(array $attributes)
	{
		if (is_null($instance = $this->where($attributes)->first()))
		{
			$instance = $this->related->newInstance($attributes);
			$instance->setAttribute($this->getPlainForeignKey(), $this->getParentKey());
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
		$instance->setAttribute($this->getPlainForeignKey(), $this->getParentKey());
		$instance->save();
		return $instance;
	}
	public function createMany(array $records)
	{
		$instances = array();
		foreach ($records as $record)
		{
			$instances[] = $this->create($record);
		}
		return $instances;
	}
	public function update(array $attributes)
	{
		if ($this->related->usesTimestamps())
		{
			$attributes[$this->relatedUpdatedAt()] = $this->related->freshTimestampString();
		}
		return $this->query->update($attributes);
	}
	public function getHasCompareKey()
	{
		return $this->getForeignKey();
	}
	public function getForeignKey()
	{
		return $this->foreignKey;
	}
	public function getPlainForeignKey()
	{
		$segments = explode('.', $this->getForeignKey());
		return $segments[count($segments) - 1];
	}
	public function getParentKey()
	{
		return $this->parent->getAttribute($this->localKey);
	}
	public function getQualifiedParentKeyName()
	{
		return $this->parent->getTable().'.'.$this->localKey;
	}
}
