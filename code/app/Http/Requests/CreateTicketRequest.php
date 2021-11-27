<?php
namespace App\Http\Requests;
use App\Http\Requests\Request;
class CreateTicketRequest extends Request {
	public function authorize() {
		return true;
	}
	public function rules() {
		return [
			'email' => 'required|email',
			'fullname' => 'required|min:3',
			'helptopic' => 'required',
			'dept' => 'required',
			'sla' => 'required',
			'subject' => 'required|min:5',
			'body' => 'required|min:20',
			'priority' => 'required',
		];
	}
}
