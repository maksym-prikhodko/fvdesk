<?php
namespace App\Http\Requests;
use App\Http\Requests\Request;
class HelptopicRequest extends Request {
	public function authorize() {
		return true;
	}
	public function rules() {
		return [
			'topic' => 'required|unique:help_topic',
			'parent_topic' => 'required',
			'custom_form' => 'required',
			'department' => 'required',
			'priority' => 'required',
			'sla_plan' => 'required',
			'auto_assign' => 'required',
		];
	}
}
