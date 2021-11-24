<?php namespace App\Http\Requests;
use App\Http\Requests\Request;
class TicketRequest extends Request {
	public function authorize()
	{
		return true;
	}
	public function rules()
	{
		return [
			 'To'    =>   'required',  
   'ticket_ID'     =>   'required',
   'ReplyContent'  =>   'required'  
		];
	}
}
