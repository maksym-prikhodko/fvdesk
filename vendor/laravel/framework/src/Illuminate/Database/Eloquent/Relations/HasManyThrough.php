<?php namespace Illuminate\Database\Eloquent\Relations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Expression;
use Illuminate\Database\Eloquent\Collection;
class HasManyThrough extends Relation {
	protected $farParent;
	protected $firstKey;
	protected $secondKey;
	public function __construct(Builder $query, Model $farParent, Model $parent, $firstKey, $secondKey)
	{
		$this->firstKey = $firstKey;
		$this->secondKey = $secondKey;
		$this->farParent = $farParent;
		parent::__construct($query, $parent);
	}
	public function addConstraints()
	{
		$parentTable = $this->parent->getTable();
		$this->setJoin();
		if (static::$constraints)
		{
			$this->query->where($parentTable.'.'.$this->firstKey, '=', $this->farParent->getKey());
		}
	}
	public function getRelationCountQuery(Builder $query, Builder $parent)
	{
		$parentTable = $this->parent->getTable();
		$this->setJoin($query);
		$query->select(new Expression('count(*)'));
		$key = $this->wrap($parentTable.'.'.$this->firstKey);
		return $query->where($this->getHasCompareKey(), '=', new Expression($key));
	}
	protected function setJoin(Builder $query = null)
	{
		$query = $query ?: $this->query;
		$foreignKey = $this->related->getTable().'.'.$this->secondKey;
		$query->join($this->parent->getTable(), $this->getQualifiedParentKeyName(), '=', $foreignKey);
		if ($this->parentSoftDeletes())
		{
			$query->whereNull($this->parent->getQualifiedDeletedAtColumn());
		}
	}
	public function parentSoftDeletes()
	{
		return in_array('Illuminate\Database\Eloquent\SoftDeletes', class_uses_recursive(get_class($this->parent)));
	}
	public function addEagerConstraints(array $models)
	{
		$table = $this->parent->getTable();
		$this->query->whereIn($table.'.'.$this->firstKey, $this->getKeys($models));
	}
	public function initRelation(array $models, $relation)
	{
		foreach ($models as $model)
		{
			$model->setRelation($relation, $this->related->newCollection());
		}
		return $models;
	}
	public function match(array $models, Collection $results, $relation)
	{
		$dictionary = $this->buildDictionary($results);
		foreach ($models as $model)
		{
			$key = $model->getKey();
			if (isset($dictionary[$key]))
			{
				$value = $this->related->newCollection($dictionary[$key]);
				$model->setRelation($relation, $value);
			}
		}
		return $models;
	}
	protected function buildDictionary(Collection $results)
	{
		$dictionary = [];
		$foreign = $this->firstKey;
		foreach ($results as $result)
		{
			$dictionary[$result->{$foreign}][] = $result;
		}
		return $dictionary;
	}
	public function getResults()
	{
		return $this->get();
	}
	public function first($columns = ['*'])
	{
		$results = $this->take(1)->get($columns);
		return count($results) > 0 ? $results->first() : null;
	}
	public function find($id, $columns = ['*'])
	{
		if (is_array($id))
		{
			return $this->findMany($id, $columns);
		}
		$this->where($this->getRelated()->getQualifiedKeyName(), '=', $id);
		return $this->first($columns);
	}
	public function findMany($ids, $columns = ['*'])
	{
		if (empty($ids)) return $this->getRelated()->newCollection();
		$this->whereIn($this->getRelated()->getQualifiedKeyName(), $ids);
		return $this->get($columns);
	}
	public function get($columns = ['*'])
	{
		$columns = $this->query->getQuery()->columns ? [] : $columns;
		$select = $this->getSelectColumns($columns);
		$models = $this->query->addSelect($select)->getModels();
		if (count($models) > 0)
		{
			$models = $this->query->eagerLoadRelations($models);
		}
		return $this->related->newCollection($models);
	}
	protected function getSelectColumns(array $columns = ['*'])
	{
		if ($columns == ['*'])
		{
			$columns = [$this->related->getTable().'.*'];
		}
		return array_merge($columns, [$this->parent->getTable().'.'.$this->firstKey]);
	}
	public function paginate($perPage = null, $columns = ['*'])
	{
		$this->query->addSelect($this->getSelectColumns($columns));
		return $this->query->paginate($perPage, $columns);
	}
	public function simplePaginate($perPage = null, $columns = ['*'])
	{
		$this->query->addSelect($this->getSelectColumns($columns));
		return $this->query->simplePaginate($perPage, $columns);
	}
	public function getHasCompareKey()
	{
		return $this->farParent->getQualifiedKeyName();
	}
}
