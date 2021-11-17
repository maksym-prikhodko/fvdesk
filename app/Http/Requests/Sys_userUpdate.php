<?php namespace App\Http\Requests;
use App\Http\Requests\Request;
class Sys_userUpdate extends Request {
	public function authorize()
	{
		return true;
	}
	public function rules()
	{
		return [
			'email' 		=> 	'required|email',
			'phone'			=>	'size:10'
		];
	}
}
