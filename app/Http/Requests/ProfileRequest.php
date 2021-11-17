<?php namespace App\Http\Requests;
use App\Http\Requests\Request;
class ProfileRequest extends Request {
	public function authorize()
	{
		return true;
	}
	public function rules()
	{
		return [
			'first_name'	=>	'required',
			'profile_pic' 	=> 'mimes:png,jpeg',
		];
	}
}
