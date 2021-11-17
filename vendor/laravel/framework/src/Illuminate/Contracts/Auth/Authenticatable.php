<?php namespace Illuminate\Contracts\Auth;
interface Authenticatable {
	public function getAuthIdentifier();
	public function getAuthPassword();
	public function getRememberToken();
	public function setRememberToken($value);
	public function getRememberTokenName();
}
