<?php
namespace App\Http\Requests;
use App\Http\Requests\Request;
class DepartmentUpdate extends Request {
	public function authorize() {
		return true;
	}
	public function rules() {
		return [
			'outgoing_email' => 'required',
			'auto_response_email' => 'required',
			'group_id' => 'required',
		];
	}
}
