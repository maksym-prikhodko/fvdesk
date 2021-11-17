<?php namespace Illuminate\Database\Eloquent\Relations;
use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Expression;
use Illuminate\Database\Eloquent\Collection;
abstract class Relation {
	protected $query;
	protected $parent;
	protected $related;
	protected static $constraints = true;
	public function __construct(Builder $query, Model $parent)
	{
		$this->query = $query;
		$this->parent = $parent;
		$this->related = $query->getModel();
		$this->addConstraints();
	}
	abstract public function addConstraints();
	abstract public function addEagerConstraints(array $models);
	abstract public function initRelation(array $models, $relation);
	abstract public function match(array $models, Collection $results, $relation);
	abstract public function getResults();
	public function getEager()
	{
		return $this->get();
	}
	public function touch()
	{
		$column = $this->getRelated()->getUpdatedAtColumn();
		$this->rawUpdate(array($column => $this->getRelated()->freshTimestampString()));
	}
	public function rawUpdate(array $attributes = array())
	{
		return $this->query->update($attributes);
	}
	public function getRelationCountQuery(Builder $query, Builder $parent)
	{
		$query->select(new Expression('count(*)'));
		$key = $this->wrap($this->getQualifiedParentKeyName());
		return $query->where($this->getHasCompareKey(), '=', new Expression($key));
	}
	public static function noConstraints(Closure $callback)
	{
		$previous = static::$constraints;
		static::$constraints = false;
		$results = call_user_func($callback);
		static::$constraints = $previous;
		return $results;
	}
	protected function getKeys(array $models, $key = null)
	{
		return array_unique(array_values(array_map(function($value) use ($key)
		{
			return $key ? $value->getAttribute($key) : $value->getKey();
		}, $models)));
	}
	public function getQuery()
	{
		return $this->query;
	}
	public function getBaseQuery()
	{
		return $this->query->getQuery();
	}
	public function getParent()
	{
		return $this->parent;
	}
	public function getQualifiedParentKeyName()
	{
		return $this->parent->getQualifiedKeyName();
	}
	public function getRelated()
	{
		return $this->related;
	}
	public function createdAt()
	{
		return $this->parent->getCreatedAtColumn();
	}
	public function updatedAt()
	{
		return $this->parent->getUpdatedAtColumn();
	}
	public function relatedUpdatedAt()
	{
		return $this->related->getUpdatedAtColumn();
	}
	public function wrap($value)
	{
		return $this->parent->newQueryWithoutScopes()->getQuery()->getGrammar()->wrap($value);
	}
	public function __call($method, $parameters)
	{
		$result = call_user_func_array(array($this->query, $method), $parameters);
		if ($result === $this->query) return $this;
		return $result;
	}
}
