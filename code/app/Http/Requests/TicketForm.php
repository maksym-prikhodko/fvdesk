<?php
namespace App\Http\Requests;
use App\Http\Requests\Request;
class TicketForm extends Request {
	public function authorize() {
		return true;
	}
	public function rules() {
		return [
			'help_topic' => 'required',
			'Email' => 'required',
			'Subject' => 'required',
			'Detail' => 'required',
		];
	}
}
