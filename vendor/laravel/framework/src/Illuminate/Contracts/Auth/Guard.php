<?php namespace Illuminate\Contracts\Auth;
interface Guard {
	public function check();
	public function guest();
	public function user();
	public function once(array $credentials = array());
	public function attempt(array $credentials = array(), $remember = false, $login = true);
	public function basic($field = 'email');
	public function onceBasic($field = 'email');
	public function validate(array $credentials = array());
	public function login(Authenticatable $user, $remember = false);
	public function loginUsingId($id, $remember = false);
	public function viaRemember();
	public function logout();
}
