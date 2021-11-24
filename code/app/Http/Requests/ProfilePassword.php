<?php namespace App\Http\Requests;
use App\Http\Requests\Request;
class ProfilePassword extends Request {
	public function authorize()
	{
		return true;
	}
	public function rules()
	{
		return [
			'old_password'	=>	'required',
			'new_password'	=>	'required|min:6',
			'confirm_password'	=>	'required|same:new_password'
		];
	}
}
