<?php namespace Illuminate\Auth;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Hashing\Hasher as HasherContract;
use Illuminate\Contracts\Auth\Authenticatable as UserContract;
class EloquentUserProvider implements UserProvider {
	protected $hasher;
	protected $model;
	public function __construct(HasherContract $hasher, $model)
	{
		$this->model = $model;
		$this->hasher = $hasher;
	}
	public function retrieveById($identifier)
	{
		return $this->createModel()->newQuery()->find($identifier);
	}
	public function retrieveByToken($identifier, $token)
	{
		$model = $this->createModel();
		return $model->newQuery()
                        ->where($model->getKeyName(), $identifier)
                        ->where($model->getRememberTokenName(), $token)
                        ->first();
	}
	public function updateRememberToken(UserContract $user, $token)
	{
		$user->setRememberToken($token);
		$user->save();
	}
	public function retrieveByCredentials(array $credentials)
	{
		$query = $this->createModel()->newQuery();
		foreach ($credentials as $key => $value)
		{
			if ( ! str_contains($key, 'password')) $query->where($key, $value);
		}
		return $query->first();
	}
	public function validateCredentials(UserContract $user, array $credentials)
	{
		$plain = $credentials['password'];
		return $this->hasher->check($plain, $user->getAuthPassword());
	}
	public function createModel()
	{
		$class = '\\'.ltrim($this->model, '\\');
		return new $class;
	}
}
