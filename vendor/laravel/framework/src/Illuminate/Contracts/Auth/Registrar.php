<?php namespace Illuminate\Contracts\Auth;
interface Registrar {
	public function validator(array $data);
	public function create(array $data);
}
