<?php namespace Illuminate\Database\Eloquent\Relations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as BaseCollection;
class MorphTo extends BelongsTo {
	protected $morphType;
	protected $models;
	protected $dictionary = array();
	protected $withTrashed = false;
	public function __construct(Builder $query, Model $parent, $foreignKey, $otherKey, $type, $relation)
	{
		$this->morphType = $type;
		parent::__construct($query, $parent, $foreignKey, $otherKey, $relation);
	}
	public function addEagerConstraints(array $models)
	{
		$this->buildDictionary($this->models = Collection::make($models));
	}
	protected function buildDictionary(Collection $models)
	{
		foreach ($models as $model)
		{
			if ($model->{$this->morphType})
			{
				$this->dictionary[$model->{$this->morphType}][$model->{$this->foreignKey}][] = $model;
			}
		}
	}
	public function match(array $models, Collection $results, $relation)
	{
		return $models;
	}
	public function associate(Model $model)
	{
		$this->parent->setAttribute($this->foreignKey, $model->getKey());
		$this->parent->setAttribute($this->morphType, $model->getMorphClass());
		return $this->parent->setRelation($this->relation, $model);
	}
	public function getEager()
	{
		foreach (array_keys($this->dictionary) as $type)
		{
			$this->matchToMorphParents($type, $this->getResultsByType($type));
		}
		return $this->models;
	}
	protected function matchToMorphParents($type, Collection $results)
	{
		foreach ($results as $result)
		{
			if (isset($this->dictionary[$type][$result->getKey()]))
			{
				foreach ($this->dictionary[$type][$result->getKey()] as $model)
				{
					$model->setRelation($this->relation, $result);
				}
			}
		}
	}
	protected function getResultsByType($type)
	{
		$instance = $this->createModelByType($type);
		$key = $instance->getKeyName();
		$query = $instance->newQuery();
		$query = $this->useWithTrashed($query);
		return $query->whereIn($key, $this->gatherKeysByType($type)->all())->get();
	}
	protected function gatherKeysByType($type)
	{
		$foreign = $this->foreignKey;
		return BaseCollection::make($this->dictionary[$type])->map(function($models) use ($foreign)
		{
			return head($models)->{$foreign};
		})->unique();
	}
	public function createModelByType($type)
	{
		return new $type;
	}
	public function getMorphType()
	{
		return $this->morphType;
	}
	public function getDictionary()
	{
		return $this->dictionary;
	}
	public function withTrashed()
	{
		$this->withTrashed = true;
		$this->query = $this->useWithTrashed($this->query);
		return $this;
	}
	protected function useWithTrashed(Builder $query)
	{
		if ($this->withTrashed && $query->getMacro('withTrashed') !== null)
		{
			return $query->withTrashed();
		}
		return $query;
	}
}
