<?php namespace App\Http\Requests;
use App\Http\Requests\Request;
class OrganizationUpdate extends Request {
	public function authorize()
	{
		return true;
	}
	public function rules()
	{
		return [
			'website'	=>	'url',
			'phone'		=>	'size:10'
		];
	}
}
