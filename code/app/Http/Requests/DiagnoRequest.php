<?php namespace App\Http\Requests;
use App\Http\Requests\Request;
class DiagnoRequest extends Request {
	public function authorize()
	{
		return true;
	}
	public function rules()
	{
		return [
			'from'		=>		'required|email',
			'to'		=>		'required|email',
			'subject'	=>		'required',
			'message'	=>		'required'
		];
	}
}
