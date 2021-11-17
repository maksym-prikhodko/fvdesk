<?php namespace Illuminate\Database\Eloquent;
use DateTime;
use Exception;
use ArrayAccess;
use Carbon\Carbon;
use LogicException;
use JsonSerializable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Routing\UrlRoutable;
use Illuminate\Contracts\Queue\QueueableEntity;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\ConnectionResolverInterface as Resolver;
abstract class Model implements ArrayAccess, Arrayable, Jsonable, JsonSerializable, QueueableEntity, UrlRoutable {
	protected $connection;
	protected $table;
	protected $primaryKey = 'id';
	protected $perPage = 15;
	public $incrementing = true;
	public $timestamps = true;
	protected $attributes = array();
	protected $original = array();
	protected $relations = array();
	protected $hidden = array();
	protected $visible = array();
	protected $appends = array();
	protected $fillable = array();
	protected $guarded = array('*');
	protected $dates = array();
	protected $casts = array();
	protected $touches = array();
	protected $observables = array();
	protected $with = array();
	protected $morphClass;
	public $exists = false;
	public static $snakeAttributes = true;
	protected static $resolver;
	protected static $dispatcher;
	protected static $booted = array();
	protected static $globalScopes = array();
	protected static $unguarded = false;
	protected static $mutatorCache = array();
	public static $manyMethods = array('belongsToMany', 'morphToMany', 'morphedByMany');
	const CREATED_AT = 'created_at';
	const UPDATED_AT = 'updated_at';
	public function __construct(array $attributes = array())
	{
		$this->bootIfNotBooted();
		$this->syncOriginal();
		$this->fill($attributes);
	}
	protected function bootIfNotBooted()
	{
		$class = get_class($this);
		if ( ! isset(static::$booted[$class]))
		{
			static::$booted[$class] = true;
			$this->fireModelEvent('booting', false);
			static::boot();
			$this->fireModelEvent('booted', false);
		}
	}
	protected static function boot()
	{
		static::bootTraits();
	}
	protected static function bootTraits()
	{
		foreach (class_uses_recursive(get_called_class()) as $trait)
		{
			if (method_exists(get_called_class(), $method = 'boot'.class_basename($trait)))
			{
				forward_static_call([get_called_class(), $method]);
			}
		}
	}
	public static function addGlobalScope(ScopeInterface $scope)
	{
		static::$globalScopes[get_called_class()][get_class($scope)] = $scope;
	}
	public static function hasGlobalScope($scope)
	{
		return ! is_null(static::getGlobalScope($scope));
	}
	public static function getGlobalScope($scope)
	{
		return array_first(static::$globalScopes[get_called_class()], function($key, $value) use ($scope)
		{
			return $scope instanceof $value;
		});
	}
	public function getGlobalScopes()
	{
		return array_get(static::$globalScopes, get_class($this), []);
	}
	public static function observe($class)
	{
		$instance = new static;
		$className = is_string($class) ? $class : get_class($class);
		foreach ($instance->getObservableEvents() as $event)
		{
			if (method_exists($class, $event))
			{
				static::registerModelEvent($event, $className.'@'.$event);
			}
		}
	}
	public function fill(array $attributes)
	{
		$totallyGuarded = $this->totallyGuarded();
		foreach ($this->fillableFromArray($attributes) as $key => $value)
		{
			$key = $this->removeTableFromKey($key);
			if ($this->isFillable($key))
			{
				$this->setAttribute($key, $value);
			}
			elseif ($totallyGuarded)
			{
				throw new MassAssignmentException($key);
			}
		}
		return $this;
	}
	public function forceFill(array $attributes)
	{
		$model = $this;
		return static::unguarded(function() use ($model, $attributes)
		{
			return $model->fill($attributes);
		});
	}
	protected function fillableFromArray(array $attributes)
	{
		if (count($this->fillable) > 0 && ! static::$unguarded)
		{
			return array_intersect_key($attributes, array_flip($this->fillable));
		}
		return $attributes;
	}
	public function newInstance($attributes = array(), $exists = false)
	{
		$model = new static((array) $attributes);
		$model->exists = $exists;
		return $model;
	}
	public function newFromBuilder($attributes = array(), $connection = null)
	{
		$model = $this->newInstance(array(), true);
		$model->setRawAttributes((array) $attributes, true);
		$model->setConnection($connection ?: $this->connection);
		return $model;
	}
	public static function hydrate(array $items, $connection = null)
	{
		$instance = (new static)->setConnection($connection);
		$items = array_map(function ($item) use ($instance)
		{
			return $instance->newFromBuilder($item);
		}, $items);
		return $instance->newCollection($items);
	}
	public static function hydrateRaw($query, $bindings = array(), $connection = null)
	{
		$instance = (new static)->setConnection($connection);
		$items = $instance->getConnection()->select($query, $bindings);
		return static::hydrate($items, $connection);
	}
	public static function create(array $attributes)
	{
		$model = new static($attributes);
		$model->save();
		return $model;
	}
	public static function forceCreate(array $attributes)
	{
		$model = new static;
		return static::unguarded(function() use ($model, $attributes)
		{
			return $model->create($attributes);
		});
	}
	public static function firstOrCreate(array $attributes)
	{
		if ( ! is_null($instance = static::where($attributes)->first()))
		{
			return $instance;
		}
		return static::create($attributes);
	}
	public static function firstOrNew(array $attributes)
	{
		if ( ! is_null($instance = static::where($attributes)->first()))
		{
			return $instance;
		}
		return new static($attributes);
	}
	public static function updateOrCreate(array $attributes, array $values = array())
	{
		$instance = static::firstOrNew($attributes);
		$instance->fill($values)->save();
		return $instance;
	}
	protected static function firstByAttributes($attributes)
	{
		return static::where($attributes)->first();
	}
	public static function query()
	{
		return (new static)->newQuery();
	}
	public static function on($connection = null)
	{
		$instance = new static;
		$instance->setConnection($connection);
		return $instance->newQuery();
	}
	public static function onWriteConnection()
	{
		$instance = new static;
		return $instance->newQuery()->useWritePdo();
	}
	public static function all($columns = array('*'))
	{
		$instance = new static;
		return $instance->newQuery()->get($columns);
	}
	public static function find($id, $columns = array('*'))
	{
		return static::query()->find($id, $columns);
	}
	public static function findOrNew($id, $columns = array('*'))
	{
		if ( ! is_null($model = static::find($id, $columns))) return $model;
		return new static;
	}
	public function fresh(array $with = array())
	{
		if ( ! $this->exists) return;
		$key = $this->getKeyName();
		return static::with($with)->where($key, $this->getKey())->first();
	}
	public function load($relations)
	{
		if (is_string($relations)) $relations = func_get_args();
		$query = $this->newQuery()->with($relations);
		$query->eagerLoadRelations(array($this));
		return $this;
	}
	public static function with($relations)
	{
		if (is_string($relations)) $relations = func_get_args();
		$instance = new static;
		return $instance->newQuery()->with($relations);
	}
	public function hasOne($related, $foreignKey = null, $localKey = null)
	{
		$foreignKey = $foreignKey ?: $this->getForeignKey();
		$instance = new $related;
		$localKey = $localKey ?: $this->getKeyName();
		return new HasOne($instance->newQuery(), $this, $instance->getTable().'.'.$foreignKey, $localKey);
	}
	public function morphOne($related, $name, $type = null, $id = null, $localKey = null)
	{
		$instance = new $related;
		list($type, $id) = $this->getMorphs($name, $type, $id);
		$table = $instance->getTable();
		$localKey = $localKey ?: $this->getKeyName();
		return new MorphOne($instance->newQuery(), $this, $table.'.'.$type, $table.'.'.$id, $localKey);
	}
	public function belongsTo($related, $foreignKey = null, $otherKey = null, $relation = null)
	{
		if (is_null($relation))
		{
			list(, $caller) = debug_backtrace(false, 2);
			$relation = $caller['function'];
		}
		if (is_null($foreignKey))
		{
			$foreignKey = snake_case($relation).'_id';
		}
		$instance = new $related;
		$query = $instance->newQuery();
		$otherKey = $otherKey ?: $instance->getKeyName();
		return new BelongsTo($query, $this, $foreignKey, $otherKey, $relation);
	}
	public function morphTo($name = null, $type = null, $id = null)
	{
		if (is_null($name))
		{
			list(, $caller) = debug_backtrace(false, 2);
			$name = snake_case($caller['function']);
		}
		list($type, $id) = $this->getMorphs($name, $type, $id);
		if (is_null($class = $this->$type))
		{
			return new MorphTo(
				$this->newQuery(), $this, $id, null, $type, $name
			);
		}
		else
		{
			$instance = new $class;
			return new MorphTo(
				$instance->newQuery(), $this, $id, $instance->getKeyName(), $type, $name
			);
		}
	}
	public function hasMany($related, $foreignKey = null, $localKey = null)
	{
		$foreignKey = $foreignKey ?: $this->getForeignKey();
		$instance = new $related;
		$localKey = $localKey ?: $this->getKeyName();
		return new HasMany($instance->newQuery(), $this, $instance->getTable().'.'.$foreignKey, $localKey);
	}
	public function hasManyThrough($related, $through, $firstKey = null, $secondKey = null)
	{
		$through = new $through;
		$firstKey = $firstKey ?: $this->getForeignKey();
		$secondKey = $secondKey ?: $through->getForeignKey();
		return new HasManyThrough((new $related)->newQuery(), $this, $through, $firstKey, $secondKey);
	}
	public function morphMany($related, $name, $type = null, $id = null, $localKey = null)
	{
		$instance = new $related;
		list($type, $id) = $this->getMorphs($name, $type, $id);
		$table = $instance->getTable();
		$localKey = $localKey ?: $this->getKeyName();
		return new MorphMany($instance->newQuery(), $this, $table.'.'.$type, $table.'.'.$id, $localKey);
	}
	public function belongsToMany($related, $table = null, $foreignKey = null, $otherKey = null, $relation = null)
	{
		if (is_null($relation))
		{
			$relation = $this->getBelongsToManyCaller();
		}
		$foreignKey = $foreignKey ?: $this->getForeignKey();
		$instance = new $related;
		$otherKey = $otherKey ?: $instance->getForeignKey();
		if (is_null($table))
		{
			$table = $this->joiningTable($related);
		}
		$query = $instance->newQuery();
		return new BelongsToMany($query, $this, $table, $foreignKey, $otherKey, $relation);
	}
	public function morphToMany($related, $name, $table = null, $foreignKey = null, $otherKey = null, $inverse = false)
	{
		$caller = $this->getBelongsToManyCaller();
		$foreignKey = $foreignKey ?: $name.'_id';
		$instance = new $related;
		$otherKey = $otherKey ?: $instance->getForeignKey();
		$query = $instance->newQuery();
		$table = $table ?: str_plural($name);
		return new MorphToMany(
			$query, $this, $name, $table, $foreignKey,
			$otherKey, $caller, $inverse
		);
	}
	public function morphedByMany($related, $name, $table = null, $foreignKey = null, $otherKey = null)
	{
		$foreignKey = $foreignKey ?: $this->getForeignKey();
		$otherKey = $otherKey ?: $name.'_id';
		return $this->morphToMany($related, $name, $table, $foreignKey, $otherKey, true);
	}
	protected function getBelongsToManyCaller()
	{
		$self = __FUNCTION__;
		$caller = array_first(debug_backtrace(false), function($key, $trace) use ($self)
		{
			$caller = $trace['function'];
			return ! in_array($caller, Model::$manyMethods) && $caller != $self;
		});
		return ! is_null($caller) ? $caller['function'] : null;
	}
	public function joiningTable($related)
	{
		$base = snake_case(class_basename($this));
		$related = snake_case(class_basename($related));
		$models = array($related, $base);
		sort($models);
		return strtolower(implode('_', $models));
	}
	public static function destroy($ids)
	{
		$count = 0;
		$ids = is_array($ids) ? $ids : func_get_args();
		$instance = new static;
		$key = $instance->getKeyName();
		foreach ($instance->whereIn($key, $ids)->get() as $model)
		{
			if ($model->delete()) $count++;
		}
		return $count;
	}
	public function delete()
	{
		if (is_null($this->primaryKey))
		{
			throw new Exception("No primary key defined on model.");
		}
		if ($this->exists)
		{
			if ($this->fireModelEvent('deleting') === false) return false;
			$this->touchOwners();
			$this->performDeleteOnModel();
			$this->exists = false;
			$this->fireModelEvent('deleted', false);
			return true;
		}
	}
	public function forceDelete()
	{
		return $this->delete();
	}
	protected function performDeleteOnModel()
	{
		$this->setKeysForSaveQuery($this->newQuery())->delete();
	}
	public static function saving($callback, $priority = 0)
	{
		static::registerModelEvent('saving', $callback, $priority);
	}
	public static function saved($callback, $priority = 0)
	{
		static::registerModelEvent('saved', $callback, $priority);
	}
	public static function updating($callback, $priority = 0)
	{
		static::registerModelEvent('updating', $callback, $priority);
	}
	public static function updated($callback, $priority = 0)
	{
		static::registerModelEvent('updated', $callback, $priority);
	}
	public static function creating($callback, $priority = 0)
	{
		static::registerModelEvent('creating', $callback, $priority);
	}
	public static function created($callback, $priority = 0)
	{
		static::registerModelEvent('created', $callback, $priority);
	}
	public static function deleting($callback, $priority = 0)
	{
		static::registerModelEvent('deleting', $callback, $priority);
	}
	public static function deleted($callback, $priority = 0)
	{
		static::registerModelEvent('deleted', $callback, $priority);
	}
	public static function flushEventListeners()
	{
		if ( ! isset(static::$dispatcher)) return;
		$instance = new static;
		foreach ($instance->getObservableEvents() as $event)
		{
			static::$dispatcher->forget("eloquent.{$event}: ".get_called_class());
		}
	}
	protected static function registerModelEvent($event, $callback, $priority = 0)
	{
		if (isset(static::$dispatcher))
		{
			$name = get_called_class();
			static::$dispatcher->listen("eloquent.{$event}: {$name}", $callback, $priority);
		}
	}
	public function getObservableEvents()
	{
		return array_merge(
			array(
				'creating', 'created', 'updating', 'updated',
				'deleting', 'deleted', 'saving', 'saved',
				'restoring', 'restored',
			),
			$this->observables
		);
	}
	public function setObservableEvents(array $observables)
	{
		$this->observables = $observables;
	}
	public function addObservableEvents($observables)
	{
		$observables = is_array($observables) ? $observables : func_get_args();
		$this->observables = array_unique(array_merge($this->observables, $observables));
	}
	public function removeObservableEvents($observables)
	{
		$observables = is_array($observables) ? $observables : func_get_args();
		$this->observables = array_diff($this->observables, $observables);
	}
	protected function increment($column, $amount = 1)
	{
		return $this->incrementOrDecrement($column, $amount, 'increment');
	}
	protected function decrement($column, $amount = 1)
	{
		return $this->incrementOrDecrement($column, $amount, 'decrement');
	}
	protected function incrementOrDecrement($column, $amount, $method)
	{
		$query = $this->newQuery();
		if ( ! $this->exists)
		{
			return $query->{$method}($column, $amount);
		}
		$this->incrementOrDecrementAttributeValue($column, $amount, $method);
		return $query->where($this->getKeyName(), $this->getKey())->{$method}($column, $amount);
	}
	protected function incrementOrDecrementAttributeValue($column, $amount, $method)
	{
		$this->{$column} = $this->{$column} + ($method == 'increment' ? $amount : $amount * -1);
		$this->syncOriginalAttribute($column);
	}
	public function update(array $attributes = array())
	{
		if ( ! $this->exists)
		{
			return $this->newQuery()->update($attributes);
		}
		return $this->fill($attributes)->save();
	}
	public function push()
	{
		if ( ! $this->save()) return false;
		foreach ($this->relations as $models)
		{
			$models = $models instanceof Collection
						? $models->all() : array($models);
			foreach (array_filter($models) as $model)
			{
				if ( ! $model->push()) return false;
			}
		}
		return true;
	}
	public function save(array $options = array())
	{
		$query = $this->newQueryWithoutScopes();
		if ($this->fireModelEvent('saving') === false)
		{
			return false;
		}
		if ($this->exists)
		{
			$saved = $this->performUpdate($query, $options);
		}
		else
		{
			$saved = $this->performInsert($query, $options);
		}
		if ($saved) $this->finishSave($options);
		return $saved;
	}
	protected function finishSave(array $options)
	{
		$this->fireModelEvent('saved', false);
		$this->syncOriginal();
		if (array_get($options, 'touch', true)) $this->touchOwners();
	}
	protected function performUpdate(Builder $query, array $options = [])
	{
		$dirty = $this->getDirty();
		if (count($dirty) > 0)
		{
			if ($this->fireModelEvent('updating') === false)
			{
				return false;
			}
			if ($this->timestamps && array_get($options, 'timestamps', true))
			{
				$this->updateTimestamps();
			}
			$dirty = $this->getDirty();
			if (count($dirty) > 0)
			{
				$this->setKeysForSaveQuery($query)->update($dirty);
				$this->fireModelEvent('updated', false);
			}
		}
		return true;
	}
	protected function performInsert(Builder $query, array $options = [])
	{
		if ($this->fireModelEvent('creating') === false) return false;
		if ($this->timestamps && array_get($options, 'timestamps', true))
		{
			$this->updateTimestamps();
		}
		$attributes = $this->attributes;
		if ($this->incrementing)
		{
			$this->insertAndSetId($query, $attributes);
		}
		else
		{
			$query->insert($attributes);
		}
		$this->exists = true;
		$this->fireModelEvent('created', false);
		return true;
	}
	protected function insertAndSetId(Builder $query, $attributes)
	{
		$id = $query->insertGetId($attributes, $keyName = $this->getKeyName());
		$this->setAttribute($keyName, $id);
	}
	public function touchOwners()
	{
		foreach ($this->touches as $relation)
		{
			$this->$relation()->touch();
			if ($this->$relation instanceof Model)
			{
				$this->$relation->touchOwners();
			}
			elseif ($this->$relation instanceof Collection)
			{
				$this->$relation->each(function (Model $relation)
				{
					$relation->touchOwners();
				});
			}
		}
	}
	public function touches($relation)
	{
		return in_array($relation, $this->touches);
	}
	protected function fireModelEvent($event, $halt = true)
	{
		if ( ! isset(static::$dispatcher)) return true;
		$event = "eloquent.{$event}: ".get_class($this);
		$method = $halt ? 'until' : 'fire';
		return static::$dispatcher->$method($event, $this);
	}
	protected function setKeysForSaveQuery(Builder $query)
	{
		$query->where($this->getKeyName(), '=', $this->getKeyForSaveQuery());
		return $query;
	}
	protected function getKeyForSaveQuery()
	{
		if (isset($this->original[$this->getKeyName()]))
		{
			return $this->original[$this->getKeyName()];
		}
		return $this->getAttribute($this->getKeyName());
	}
	public function touch()
	{
		if ( ! $this->timestamps) return false;
		$this->updateTimestamps();
		return $this->save();
	}
	protected function updateTimestamps()
	{
		$time = $this->freshTimestamp();
		if ( ! $this->isDirty(static::UPDATED_AT))
		{
			$this->setUpdatedAt($time);
		}
		if ( ! $this->exists && ! $this->isDirty(static::CREATED_AT))
		{
			$this->setCreatedAt($time);
		}
	}
	public function setCreatedAt($value)
	{
		$this->{static::CREATED_AT} = $value;
	}
	public function setUpdatedAt($value)
	{
		$this->{static::UPDATED_AT} = $value;
	}
	public function getCreatedAtColumn()
	{
		return static::CREATED_AT;
	}
	public function getUpdatedAtColumn()
	{
		return static::UPDATED_AT;
	}
	public function freshTimestamp()
	{
		return new Carbon;
	}
	public function freshTimestampString()
	{
		return $this->fromDateTime($this->freshTimestamp());
	}
	public function newQuery()
	{
		$builder = $this->newQueryWithoutScopes();
		return $this->applyGlobalScopes($builder);
	}
	public function newQueryWithoutScope($scope)
	{
		$this->getGlobalScope($scope)->remove($builder = $this->newQuery(), $this);
		return $builder;
	}
	public function newQueryWithoutScopes()
	{
		$builder = $this->newEloquentBuilder(
			$this->newBaseQueryBuilder()
		);
		return $builder->setModel($this)->with($this->with);
	}
	public function applyGlobalScopes($builder)
	{
		foreach ($this->getGlobalScopes() as $scope)
		{
			$scope->apply($builder, $this);
		}
		return $builder;
	}
	public function removeGlobalScopes($builder)
	{
		foreach ($this->getGlobalScopes() as $scope)
		{
			$scope->remove($builder, $this);
		}
		return $builder;
	}
	public function newEloquentBuilder($query)
	{
		return new Builder($query);
	}
	protected function newBaseQueryBuilder()
	{
		$conn = $this->getConnection();
		$grammar = $conn->getQueryGrammar();
		return new QueryBuilder($conn, $grammar, $conn->getPostProcessor());
	}
	public function newCollection(array $models = array())
	{
		return new Collection($models);
	}
	public function newPivot(Model $parent, array $attributes, $table, $exists)
	{
		return new Pivot($parent, $attributes, $table, $exists);
	}
	public function getTable()
	{
		if (isset($this->table)) return $this->table;
		return str_replace('\\', '', snake_case(str_plural(class_basename($this))));
	}
	public function setTable($table)
	{
		$this->table = $table;
	}
	public function getKey()
	{
		return $this->getAttribute($this->getKeyName());
	}
	public function getQueueableId()
	{
		return $this->getKey();
	}
	public function getKeyName()
	{
		return $this->primaryKey;
	}
	public function setKeyName($key)
	{
		$this->primaryKey = $key;
	}
	public function getQualifiedKeyName()
	{
		return $this->getTable().'.'.$this->getKeyName();
	}
	public function getRouteKey()
	{
		return $this->getAttribute($this->getRouteKeyName());
	}
	public function getRouteKeyName()
	{
		return $this->getKeyName();
	}
	public function usesTimestamps()
	{
		return $this->timestamps;
	}
	protected function getMorphs($name, $type, $id)
	{
		$type = $type ?: $name.'_type';
		$id = $id ?: $name.'_id';
		return array($type, $id);
	}
	public function getMorphClass()
	{
		return $this->morphClass ?: get_class($this);
	}
	public function getPerPage()
	{
		return $this->perPage;
	}
	public function setPerPage($perPage)
	{
		$this->perPage = $perPage;
	}
	public function getForeignKey()
	{
		return snake_case(class_basename($this)).'_id';
	}
	public function getHidden()
	{
		return $this->hidden;
	}
	public function setHidden(array $hidden)
	{
		$this->hidden = $hidden;
	}
	public function addHidden($attributes = null)
	{
		$attributes = is_array($attributes) ? $attributes : func_get_args();
		$this->hidden = array_merge($this->hidden, $attributes);
	}
	public function getVisible()
	{
		return $this->visible;
	}
	public function setVisible(array $visible)
	{
		$this->visible = $visible;
	}
	public function addVisible($attributes = null)
	{
		$attributes = is_array($attributes) ? $attributes : func_get_args();
		$this->visible = array_merge($this->visible, $attributes);
	}
	public function setAppends(array $appends)
	{
		$this->appends = $appends;
	}
	public function getFillable()
	{
		return $this->fillable;
	}
	public function fillable(array $fillable)
	{
		$this->fillable = $fillable;
		return $this;
	}
	public function getGuarded()
	{
		return $this->guarded;
	}
	public function guard(array $guarded)
	{
		$this->guarded = $guarded;
		return $this;
	}
	public static function unguard($state = true)
	{
		static::$unguarded = $state;
	}
	public static function reguard()
	{
		static::$unguarded = false;
	}
	public static function isUnguarded()
	{
		return static::$unguarded;
	}
	public static function unguarded(callable $callback)
	{
		if (static::$unguarded) return $callback();
		static::unguard();
		$result = $callback();
		static::reguard();
		return $result;
	}
	public function isFillable($key)
	{
		if (static::$unguarded) return true;
		if (in_array($key, $this->fillable)) return true;
		if ($this->isGuarded($key)) return false;
		return empty($this->fillable) && ! starts_with($key, '_');
	}
	public function isGuarded($key)
	{
		return in_array($key, $this->guarded) || $this->guarded == array('*');
	}
	public function totallyGuarded()
	{
		return count($this->fillable) == 0 && $this->guarded == array('*');
	}
	protected function removeTableFromKey($key)
	{
		if ( ! str_contains($key, '.')) return $key;
		return last(explode('.', $key));
	}
	public function getTouchedRelations()
	{
		return $this->touches;
	}
	public function setTouchedRelations(array $touches)
	{
		$this->touches = $touches;
	}
	public function getIncrementing()
	{
		return $this->incrementing;
	}
	public function setIncrementing($value)
	{
		$this->incrementing = $value;
	}
	public function toJson($options = 0)
	{
		return json_encode($this->toArray(), $options);
	}
	public function jsonSerialize()
	{
		return $this->toArray();
	}
	public function toArray()
	{
		$attributes = $this->attributesToArray();
		return array_merge($attributes, $this->relationsToArray());
	}
	public function attributesToArray()
	{
		$attributes = $this->getArrayableAttributes();
		foreach ($this->getDates() as $key)
		{
			if ( ! isset($attributes[$key])) continue;
			$attributes[$key] = (string) $this->asDateTime($attributes[$key]);
		}
		$mutatedAttributes = $this->getMutatedAttributes();
		foreach ($mutatedAttributes as $key)
		{
			if ( ! array_key_exists($key, $attributes)) continue;
			$attributes[$key] = $this->mutateAttributeForArray(
				$key, $attributes[$key]
			);
		}
		foreach ($this->casts as $key => $value)
		{
			if ( ! array_key_exists($key, $attributes) ||
				in_array($key, $mutatedAttributes)) continue;
			$attributes[$key] = $this->castAttribute(
				$key, $attributes[$key]
			);
		}
		foreach ($this->getArrayableAppends() as $key)
		{
			$attributes[$key] = $this->mutateAttributeForArray($key, null);
		}
		return $attributes;
	}
	protected function getArrayableAttributes()
	{
		return $this->getArrayableItems($this->attributes);
	}
	protected function getArrayableAppends()
	{
		if ( ! count($this->appends)) return [];
		return $this->getArrayableItems(
			array_combine($this->appends, $this->appends)
		);
	}
	public function relationsToArray()
	{
		$attributes = array();
		foreach ($this->getArrayableRelations() as $key => $value)
		{
			if (in_array($key, $this->hidden)) continue;
			if ($value instanceof Arrayable)
			{
				$relation = $value->toArray();
			}
			elseif (is_null($value))
			{
				$relation = $value;
			}
			if (static::$snakeAttributes)
			{
				$key = snake_case($key);
			}
			if (isset($relation) || is_null($value))
			{
				$attributes[$key] = $relation;
			}
			unset($relation);
		}
		return $attributes;
	}
	protected function getArrayableRelations()
	{
		return $this->getArrayableItems($this->relations);
	}
	protected function getArrayableItems(array $values)
	{
		if (count($this->visible) > 0)
		{
			return array_intersect_key($values, array_flip($this->visible));
		}
		return array_diff_key($values, array_flip($this->hidden));
	}
	public function getAttribute($key)
	{
		$inAttributes = array_key_exists($key, $this->attributes);
		if ($inAttributes || $this->hasGetMutator($key))
		{
			return $this->getAttributeValue($key);
		}
		if (array_key_exists($key, $this->relations))
		{
			return $this->relations[$key];
		}
		if (method_exists($this, $key))
		{
			return $this->getRelationshipFromMethod($key);
		}
	}
	protected function getAttributeValue($key)
	{
		$value = $this->getAttributeFromArray($key);
		if ($this->hasGetMutator($key))
		{
			return $this->mutateAttribute($key, $value);
		}
		if ($this->hasCast($key))
		{
			$value = $this->castAttribute($key, $value);
		}
		elseif (in_array($key, $this->getDates()))
		{
			if ( ! is_null($value)) return $this->asDateTime($value);
		}
		return $value;
	}
	protected function getAttributeFromArray($key)
	{
		if (array_key_exists($key, $this->attributes))
		{
			return $this->attributes[$key];
		}
	}
	protected function getRelationshipFromMethod($method)
	{
		$relations = $this->$method();
		if ( ! $relations instanceof Relation)
		{
			throw new LogicException('Relationship method must return an object of type '
				. 'Illuminate\Database\Eloquent\Relations\Relation');
		}
		return $this->relations[$method] = $relations->getResults();
	}
	public function hasGetMutator($key)
	{
		return method_exists($this, 'get'.studly_case($key).'Attribute');
	}
	protected function mutateAttribute($key, $value)
	{
		return $this->{'get'.studly_case($key).'Attribute'}($value);
	}
	protected function mutateAttributeForArray($key, $value)
	{
		$value = $this->mutateAttribute($key, $value);
		return $value instanceof Arrayable ? $value->toArray() : $value;
	}
	protected function hasCast($key)
	{
		return array_key_exists($key, $this->casts);
	}
	protected function isJsonCastable($key)
	{
		if ($this->hasCast($key))
		{
			return in_array(
				$this->getCastType($key), ['array', 'json', 'object', 'collection'], true
			);
		}
		return false;
	}
	protected function getCastType($key)
	{
		return trim(strtolower($this->casts[$key]));
	}
	protected function castAttribute($key, $value)
	{
		if (is_null($value)) return $value;
		switch ($this->getCastType($key))
		{
			case 'int':
			case 'integer':
				return (int) $value;
			case 'real':
			case 'float':
			case 'double':
				return (float) $value;
			case 'string':
				return (string) $value;
			case 'bool':
			case 'boolean':
				return (bool) $value;
			case 'object':
				return json_decode($value);
			case 'array':
			case 'json':
				return json_decode($value, true);
			case 'collection':
				return $this->newCollection(json_decode($value, true));
			default:
				return $value;
		}
	}
	public function setAttribute($key, $value)
	{
		if ($this->hasSetMutator($key))
		{
			$method = 'set'.studly_case($key).'Attribute';
			return $this->{$method}($value);
		}
		elseif (in_array($key, $this->getDates()) && $value)
		{
			$value = $this->fromDateTime($value);
		}
		if ($this->isJsonCastable($key))
		{
			$value = json_encode($value);
		}
		$this->attributes[$key] = $value;
	}
	public function hasSetMutator($key)
	{
		return method_exists($this, 'set'.studly_case($key).'Attribute');
	}
	public function getDates()
	{
		$defaults = array(static::CREATED_AT, static::UPDATED_AT);
		return array_merge($this->dates, $defaults);
	}
	public function fromDateTime($value)
	{
		$format = $this->getDateFormat();
		if ($value instanceof DateTime)
		{
		}
		elseif (is_numeric($value))
		{
			$value = Carbon::createFromTimestamp($value);
		}
		elseif (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $value))
		{
			$value = Carbon::createFromFormat('Y-m-d', $value)->startOfDay();
		}
		else
		{
			$value = Carbon::createFromFormat($format, $value);
		}
		return $value->format($format);
	}
	protected function asDateTime($value)
	{
		if (is_numeric($value))
		{
			return Carbon::createFromTimestamp($value);
		}
		elseif (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $value))
		{
			return Carbon::createFromFormat('Y-m-d', $value)->startOfDay();
		}
		elseif ( ! $value instanceof DateTime)
		{
			$format = $this->getDateFormat();
			return Carbon::createFromFormat($format, $value);
		}
		return Carbon::instance($value);
	}
	protected function getDateFormat()
	{
		return $this->getConnection()->getQueryGrammar()->getDateFormat();
	}
	public function replicate(array $except = null)
	{
		$except = $except ?: [
			$this->getKeyName(),
			$this->getCreatedAtColumn(),
			$this->getUpdatedAtColumn(),
		];
		$attributes = array_except($this->attributes, $except);
		with($instance = new static)->setRawAttributes($attributes);
		return $instance->setRelations($this->relations);
	}
	public function getAttributes()
	{
		return $this->attributes;
	}
	public function setRawAttributes(array $attributes, $sync = false)
	{
		$this->attributes = $attributes;
		if ($sync) $this->syncOriginal();
	}
	public function getOriginal($key = null, $default = null)
	{
		return array_get($this->original, $key, $default);
	}
	public function syncOriginal()
	{
		$this->original = $this->attributes;
		return $this;
	}
	public function syncOriginalAttribute($attribute)
	{
		$this->original[$attribute] = $this->attributes[$attribute];
		return $this;
	}
	public function isDirty($attributes = null)
	{
		$dirty = $this->getDirty();
		if (is_null($attributes)) return count($dirty) > 0;
		if ( ! is_array($attributes)) $attributes = func_get_args();
		foreach ($attributes as $attribute)
		{
			if (array_key_exists($attribute, $dirty)) return true;
		}
		return false;
	}
	public function getDirty()
	{
		$dirty = array();
		foreach ($this->attributes as $key => $value)
		{
			if ( ! array_key_exists($key, $this->original))
			{
				$dirty[$key] = $value;
			}
			elseif ($value !== $this->original[$key] &&
                                 ! $this->originalIsNumericallyEquivalent($key))
			{
				$dirty[$key] = $value;
			}
		}
		return $dirty;
	}
	protected function originalIsNumericallyEquivalent($key)
	{
		$current = $this->attributes[$key];
		$original = $this->original[$key];
		return is_numeric($current) && is_numeric($original) && strcmp((string) $current, (string) $original) === 0;
	}
	public function getRelations()
	{
		return $this->relations;
	}
	public function getRelation($relation)
	{
		return $this->relations[$relation];
	}
	public function setRelation($relation, $value)
	{
		$this->relations[$relation] = $value;
		return $this;
	}
	public function setRelations(array $relations)
	{
		$this->relations = $relations;
		return $this;
	}
	public function getConnection()
	{
		return static::resolveConnection($this->connection);
	}
	public function getConnectionName()
	{
		return $this->connection;
	}
	public function setConnection($name)
	{
		$this->connection = $name;
		return $this;
	}
	public static function resolveConnection($connection = null)
	{
		return static::$resolver->connection($connection);
	}
	public static function getConnectionResolver()
	{
		return static::$resolver;
	}
	public static function setConnectionResolver(Resolver $resolver)
	{
		static::$resolver = $resolver;
	}
	public static function unsetConnectionResolver()
	{
		static::$resolver = null;
	}
	public static function getEventDispatcher()
	{
		return static::$dispatcher;
	}
	public static function setEventDispatcher(Dispatcher $dispatcher)
	{
		static::$dispatcher = $dispatcher;
	}
	public static function unsetEventDispatcher()
	{
		static::$dispatcher = null;
	}
	public function getMutatedAttributes()
	{
		$class = get_class($this);
		if ( ! isset(static::$mutatorCache[$class]))
		{
			static::cacheMutatedAttributes($class);
		}
		return static::$mutatorCache[$class];
	}
	public static function cacheMutatedAttributes($class)
	{
		$mutatedAttributes = array();
		foreach (get_class_methods($class) as $method)
		{
			if (strpos($method, 'Attribute') !== false &&
						preg_match('/^get(.+)Attribute$/', $method, $matches))
			{
				if (static::$snakeAttributes) $matches[1] = snake_case($matches[1]);
				$mutatedAttributes[] = lcfirst($matches[1]);
			}
		}
		static::$mutatorCache[$class] = $mutatedAttributes;
	}
	public function __get($key)
	{
		return $this->getAttribute($key);
	}
	public function __set($key, $value)
	{
		$this->setAttribute($key, $value);
	}
	public function offsetExists($offset)
	{
		return isset($this->$offset);
	}
	public function offsetGet($offset)
	{
		return $this->$offset;
	}
	public function offsetSet($offset, $value)
	{
		$this->$offset = $value;
	}
	public function offsetUnset($offset)
	{
		unset($this->$offset);
	}
	public function __isset($key)
	{
		return (isset($this->attributes[$key]) || isset($this->relations[$key])) ||
				($this->hasGetMutator($key) && ! is_null($this->getAttributeValue($key)));
	}
	public function __unset($key)
	{
		unset($this->attributes[$key], $this->relations[$key]);
	}
	public function __call($method, $parameters)
	{
		if (in_array($method, array('increment', 'decrement')))
		{
			return call_user_func_array(array($this, $method), $parameters);
		}
		$query = $this->newQuery();
		return call_user_func_array(array($query, $method), $parameters);
	}
	public static function __callStatic($method, $parameters)
	{
		$instance = new static;
		return call_user_func_array(array($instance, $method), $parameters);
	}
	public function __toString()
	{
		return $this->toJson();
	}
	public function __wakeup()
	{
		$this->bootIfNotBooted();
	}
}
