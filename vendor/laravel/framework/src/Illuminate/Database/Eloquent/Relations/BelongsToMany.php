<?php namespace Illuminate\Database\Eloquent\Relations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Expression;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
class BelongsToMany extends Relation {
	protected $table;
	protected $foreignKey;
	protected $otherKey;
	protected $relationName;
	protected $pivotColumns = array();
	protected $pivotWheres = [];
	public function __construct(Builder $query, Model $parent, $table, $foreignKey, $otherKey, $relationName = null)
	{
		$this->table = $table;
		$this->otherKey = $otherKey;
		$this->foreignKey = $foreignKey;
		$this->relationName = $relationName;
		parent::__construct($query, $parent);
	}
	public function getResults()
	{
		return $this->get();
	}
	public function wherePivot($column, $operator = null, $value = null, $boolean = 'and')
	{
		$this->pivotWheres[] = func_get_args();
		return $this->where($this->table.'.'.$column, $operator, $value, $boolean);
	}
	public function orWherePivot($column, $operator = null, $value = null)
	{
		return $this->wherePivot($column, $operator, $value, 'or');
	}
	public function first($columns = array('*'))
	{
		$results = $this->take(1)->get($columns);
		return count($results) > 0 ? $results->first() : null;
	}
	public function firstOrFail($columns = array('*'))
	{
		if ( ! is_null($model = $this->first($columns))) return $model;
		throw new ModelNotFoundException;
	}
	public function get($columns = array('*'))
	{
		$columns = $this->query->getQuery()->columns ? array() : $columns;
		$select = $this->getSelectColumns($columns);
		$models = $this->query->addSelect($select)->getModels();
		$this->hydratePivotRelation($models);
		if (count($models) > 0)
		{
			$models = $this->query->eagerLoadRelations($models);
		}
		return $this->related->newCollection($models);
	}
	public function paginate($perPage = null, $columns = array('*'))
	{
		$this->query->addSelect($this->getSelectColumns($columns));
		$paginator = $this->query->paginate($perPage, $columns);
		$this->hydratePivotRelation($paginator->items());
		return $paginator;
	}
	public function simplePaginate($perPage = null, $columns = array('*'))
	{
		$this->query->addSelect($this->getSelectColumns($columns));
		$paginator = $this->query->simplePaginate($perPage, $columns);
		$this->hydratePivotRelation($paginator->items());
		return $paginator;
	}
	public function chunk($count, callable $callback)
	{
		$this->query->addSelect($this->getSelectColumns());
		$this->query->chunk($count, function($results) use ($callback)
		{
			$this->hydratePivotRelation($results->all());
			call_user_func($callback, $results);
		});
	}
	protected function hydratePivotRelation(array $models)
	{
		foreach ($models as $model)
		{
			$pivot = $this->newExistingPivot($this->cleanPivotAttributes($model));
			$model->setRelation('pivot', $pivot);
		}
	}
	protected function cleanPivotAttributes(Model $model)
	{
		$values = array();
		foreach ($model->getAttributes() as $key => $value)
		{
			if (strpos($key, 'pivot_') === 0)
			{
				$values[substr($key, 6)] = $value;
				unset($model->$key);
			}
		}
		return $values;
	}
	public function addConstraints()
	{
		$this->setJoin();
		if (static::$constraints) $this->setWhere();
	}
	public function getRelationCountQuery(Builder $query, Builder $parent)
	{
		if ($parent->getQuery()->from == $query->getQuery()->from)
		{
			return $this->getRelationCountQueryForSelfJoin($query, $parent);
		}
		$this->setJoin($query);
		return parent::getRelationCountQuery($query, $parent);
	}
	public function getRelationCountQueryForSelfJoin(Builder $query, Builder $parent)
	{
		$query->select(new Expression('count(*)'));
		$tablePrefix = $this->query->getQuery()->getConnection()->getTablePrefix();
		$query->from($this->table.' as '.$tablePrefix.$hash = $this->getRelationCountHash());
		$key = $this->wrap($this->getQualifiedParentKeyName());
		return $query->where($hash.'.'.$this->foreignKey, '=', new Expression($key));
	}
	public function getRelationCountHash()
	{
		return 'self_'.md5(microtime(true));
	}
	protected function getSelectColumns(array $columns = array('*'))
	{
		if ($columns == array('*'))
		{
			$columns = array($this->related->getTable().'.*');
		}
		return array_merge($columns, $this->getAliasedPivotColumns());
	}
	protected function getAliasedPivotColumns()
	{
		$defaults = array($this->foreignKey, $this->otherKey);
		$columns = array();
		foreach (array_merge($defaults, $this->pivotColumns) as $column)
		{
			$columns[] = $this->table.'.'.$column.' as pivot_'.$column;
		}
		return array_unique($columns);
	}
	protected function hasPivotColumn($column)
	{
		return in_array($column, $this->pivotColumns);
	}
	protected function setJoin($query = null)
	{
		$query = $query ?: $this->query;
		$baseTable = $this->related->getTable();
		$key = $baseTable.'.'.$this->related->getKeyName();
		$query->join($this->table, $key, '=', $this->getOtherKey());
		return $this;
	}
	protected function setWhere()
	{
		$foreign = $this->getForeignKey();
		$this->query->where($foreign, '=', $this->parent->getKey());
		return $this;
	}
	public function addEagerConstraints(array $models)
	{
		$this->query->whereIn($this->getForeignKey(), $this->getKeys($models));
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
			if (isset($dictionary[$key = $model->getKey()]))
			{
				$collection = $this->related->newCollection($dictionary[$key]);
				$model->setRelation($relation, $collection);
			}
		}
		return $models;
	}
	protected function buildDictionary(Collection $results)
	{
		$foreign = $this->foreignKey;
		$dictionary = array();
		foreach ($results as $result)
		{
			$dictionary[$result->pivot->$foreign][] = $result;
		}
		return $dictionary;
	}
	public function touch()
	{
		$key = $this->getRelated()->getKeyName();
		$columns = $this->getRelatedFreshUpdate();
		$ids = $this->getRelatedIds();
		if (count($ids) > 0)
		{
			$this->getRelated()->newQuery()->whereIn($key, $ids)->update($columns);
		}
	}
	public function getRelatedIds()
	{
		$related = $this->getRelated();
		$fullKey = $related->getQualifiedKeyName();
		return $this->getQuery()->select($fullKey)->lists($related->getKeyName());
	}
	public function save(Model $model, array $joining = array(), $touch = true)
	{
		$model->save(array('touch' => false));
		$this->attach($model->getKey(), $joining, $touch);
		return $model;
	}
	public function saveMany(array $models, array $joinings = array())
	{
		foreach ($models as $key => $model)
		{
			$this->save($model, (array) array_get($joinings, $key), false);
		}
		$this->touchIfTouching();
		return $models;
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
	public function findOrNew($id, $columns = ['*'])
	{
		if (is_null($instance = $this->find($id, $columns)))
		{
			$instance = $this->getRelated()->newInstance();
		}
		return $instance;
	}
	public function firstOrNew(array $attributes)
	{
		if (is_null($instance = $this->where($attributes)->first()))
		{
			$instance = $this->related->newInstance();
		}
		return $instance;
	}
	public function firstOrCreate(array $attributes, array $joining = [], $touch = true)
	{
		if (is_null($instance = $this->where($attributes)->first()))
		{
			$instance = $this->create($attributes, $joining, $touch);
		}
		return $instance;
	}
	public function updateOrCreate(array $attributes, array $values = [], array $joining = [], $touch = true)
	{
		if (is_null($instance = $this->where($attributes)->first()))
		{
			return $this->create($values, $joining, $touch);
		}
		$instance->fill($values);
		$instance->save(['touch' => false]);
		return $instance;
	}
	public function create(array $attributes, array $joining = array(), $touch = true)
	{
		$instance = $this->related->newInstance($attributes);
		$instance->save(array('touch' => false));
		$this->attach($instance->getKey(), $joining, $touch);
		return $instance;
	}
	public function createMany(array $records, array $joinings = array())
	{
		$instances = array();
		foreach ($records as $key => $record)
		{
			$instances[] = $this->create($record, (array) array_get($joinings, $key), false);
		}
		$this->touchIfTouching();
		return $instances;
	}
	public function sync($ids, $detaching = true)
	{
		$changes = array(
			'attached' => array(), 'detached' => array(), 'updated' => array(),
		);
		if ($ids instanceof Collection) $ids = $ids->modelKeys();
		$current = $this->newPivotQuery()->lists($this->otherKey);
		$records = $this->formatSyncList($ids);
		$detach = array_diff($current, array_keys($records));
		if ($detaching && count($detach) > 0)
		{
			$this->detach($detach);
			$changes['detached'] = (array) array_map(function($v) { return (int) $v; }, $detach);
		}
		$changes = array_merge(
			$changes, $this->attachNew($records, $current, false)
		);
		if (count($changes['attached']) || count($changes['updated']))
		{
			$this->touchIfTouching();
		}
		return $changes;
	}
	protected function formatSyncList(array $records)
	{
		$results = array();
		foreach ($records as $id => $attributes)
		{
			if ( ! is_array($attributes))
			{
				list($id, $attributes) = array($attributes, array());
			}
			$results[$id] = $attributes;
		}
		return $results;
	}
	protected function attachNew(array $records, array $current, $touch = true)
	{
		$changes = array('attached' => array(), 'updated' => array());
		foreach ($records as $id => $attributes)
		{
			if ( ! in_array($id, $current))
			{
				$this->attach($id, $attributes, $touch);
				$changes['attached'][] = (int) $id;
			}
			elseif (count($attributes) > 0 &&
				$this->updateExistingPivot($id, $attributes, $touch))
			{
				$changes['updated'][] = (int) $id;
			}
		}
		return $changes;
	}
	public function updateExistingPivot($id, array $attributes, $touch = true)
	{
		if (in_array($this->updatedAt(), $this->pivotColumns))
		{
			$attributes = $this->setTimestampsOnAttach($attributes, true);
		}
		$updated = $this->newPivotStatementForId($id)->update($attributes);
		if ($touch) $this->touchIfTouching();
		return $updated;
	}
	public function attach($id, array $attributes = array(), $touch = true)
	{
		if ($id instanceof Model) $id = $id->getKey();
		$query = $this->newPivotStatement();
		$query->insert($this->createAttachRecords((array) $id, $attributes));
		if ($touch) $this->touchIfTouching();
	}
	protected function createAttachRecords($ids, array $attributes)
	{
		$records = array();
		$timed = ($this->hasPivotColumn($this->createdAt()) ||
			      $this->hasPivotColumn($this->updatedAt()));
		foreach ($ids as $key => $value)
		{
			$records[] = $this->attacher($key, $value, $attributes, $timed);
		}
		return $records;
	}
	protected function attacher($key, $value, $attributes, $timed)
	{
		list($id, $extra) = $this->getAttachId($key, $value, $attributes);
		$record = $this->createAttachRecord($id, $timed);
		return array_merge($record, $extra);
	}
	protected function getAttachId($key, $value, array $attributes)
	{
		if (is_array($value))
		{
			return array($key, array_merge($value, $attributes));
		}
		return array($value, $attributes);
	}
	protected function createAttachRecord($id, $timed)
	{
		$record[$this->foreignKey] = $this->parent->getKey();
		$record[$this->otherKey] = $id;
		if ($timed)
		{
			$record = $this->setTimestampsOnAttach($record);
		}
		return $record;
	}
	protected function setTimestampsOnAttach(array $record, $exists = false)
	{
		$fresh = $this->parent->freshTimestamp();
		if ( ! $exists && $this->hasPivotColumn($this->createdAt()))
		{
			$record[$this->createdAt()] = $fresh;
		}
		if ($this->hasPivotColumn($this->updatedAt()))
		{
			$record[$this->updatedAt()] = $fresh;
		}
		return $record;
	}
	public function detach($ids = array(), $touch = true)
	{
		if ($ids instanceof Model) $ids = (array) $ids->getKey();
		$query = $this->newPivotQuery();
		$ids = (array) $ids;
		if (count($ids) > 0)
		{
			$query->whereIn($this->otherKey, (array) $ids);
		}
		if ($touch) $this->touchIfTouching();
		$results = $query->delete();
		return $results;
	}
	public function touchIfTouching()
	{
		if ($this->touchingParent()) $this->getParent()->touch();
		if ($this->getParent()->touches($this->relationName)) $this->touch();
	}
	protected function touchingParent()
	{
		return $this->getRelated()->touches($this->guessInverseRelation());
	}
	protected function guessInverseRelation()
	{
		return camel_case(str_plural(class_basename($this->getParent())));
	}
	protected function newPivotQuery()
	{
		$query = $this->newPivotStatement();
		foreach ($this->pivotWheres as $whereArgs)
		{
			call_user_func_array([$query, 'where'], $whereArgs);
		}
		return $query->where($this->foreignKey, $this->parent->getKey());
	}
	public function newPivotStatement()
	{
		return $this->query->getQuery()->newQuery()->from($this->table);
	}
	public function newPivotStatementForId($id)
	{
		return $this->newPivotQuery()->where($this->otherKey, $id);
	}
	public function newPivot(array $attributes = array(), $exists = false)
	{
		$pivot = $this->related->newPivot($this->parent, $attributes, $this->table, $exists);
		return $pivot->setPivotKeys($this->foreignKey, $this->otherKey);
	}
	public function newExistingPivot(array $attributes = array())
	{
		return $this->newPivot($attributes, true);
	}
	public function withPivot($columns)
	{
		$columns = is_array($columns) ? $columns : func_get_args();
		$this->pivotColumns = array_merge($this->pivotColumns, $columns);
		return $this;
	}
	public function withTimestamps($createdAt = null, $updatedAt = null)
	{
		return $this->withPivot($createdAt ?: $this->createdAt(), $updatedAt ?: $this->updatedAt());
	}
	public function getRelatedFreshUpdate()
	{
		return array($this->related->getUpdatedAtColumn() => $this->related->freshTimestamp());
	}
	public function getHasCompareKey()
	{
		return $this->getForeignKey();
	}
	public function getForeignKey()
	{
		return $this->table.'.'.$this->foreignKey;
	}
	public function getOtherKey()
	{
		return $this->table.'.'.$this->otherKey;
	}
	public function getTable()
	{
		return $this->table;
	}
	public function getRelationName()
	{
		return $this->relationName;
	}
}
