<?php
namespace App\Http\Requests;
use App\Http\Requests\Request;
class EmailsEditRequest extends Request {
	public function authorize() {
		return true;
	}
	public function rules() {
		return [
			'email_address' => 'email',
			'email_name' => 'required',
			'department' => 'required',
			'priority' => 'required',
			'help_topic' => 'required',
			'imap_config' => 'required',
			'password' => 'required|min:6',
			'user_name' => 'required',
			'sending_host' => 'required',
			'sending_port' => 'required',
		];
	}
}
