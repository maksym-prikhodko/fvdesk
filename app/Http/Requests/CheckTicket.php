<?php namespace App\Http\Requests;
use App\Http\Requests\Request;
class CheckTicket extends Request {
	public function authorize()
	{
		return true;
	}
	public function rules()
	{
		return [
			'email'	=>	'required|email',
			'ticket_number'=>'required'
		];
	}
}
