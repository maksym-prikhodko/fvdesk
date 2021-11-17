<?php namespace App\Http\Requests;
use App\Http\Requests\Request;
class Sys_userRequest extends Request {
	public function authorize()
	{
		return true;
	}
	public function rules()
	{
		return [
			'email' 		=> 	'required|email',
			'full_name'		=>	'required|unique:sys_user',
			'phone'			=>	'size:10'
		];
	}
}
