<?php
namespace App\Http\Requests;
use App\Http\Requests\Request;
class RegisterRequest extends Request {
	public function authorize() {
		return true;
	}
	public function rules() {
		return [
			'email' => 'required|max:50|email|unique:users',
			'full_name' => 'required',
			'password' => 'required|min:6',
			'password_confirmation' => 'required|same:password',
		];
	}
}
