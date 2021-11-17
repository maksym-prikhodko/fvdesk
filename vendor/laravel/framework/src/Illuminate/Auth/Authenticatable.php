<?php namespace Illuminate\Auth;
trait Authenticatable {
	public function getAuthIdentifier()
	{
		return $this->getKey();
	}
	public function getAuthPassword()
	{
		return $this->password;
	}
	public function getRememberToken()
	{
		return $this->{$this->getRememberTokenName()};
	}
	public function setRememberToken($value)
	{
		$this->{$this->getRememberTokenName()} = $value;
	}
	public function getRememberTokenName()
	{
		return 'remember_token';
	}
}
