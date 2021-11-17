<?php namespace Illuminate\Database\Eloquent;
interface ScopeInterface {
	public function apply(Builder $builder, Model $model);
	public function remove(Builder $builder, Model $model);
}
