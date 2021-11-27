<?php
namespace App\Http\Requests;
use App\Http\Requests\Request;
class DepartmentRequest extends Request {
	public function authorize() {
		return true;
	}
	public function rules() {
		return [
			'name' => 'required|unique:department',
			'outgoing_email' => 'required',
			'auto_response_email' => 'required',
			'group_id' => 'required',
		];
	}
}
