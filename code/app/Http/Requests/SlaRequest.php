<?php
namespace App\Http\Requests;
use App\Http\Requests\Request;
class SlaRequest extends Request {
	public function authorize() {
		return true;
	}
	public function rules() {
		return [
			'name' => 'required|unique:sla_plan',
			'grace_period' => 'required',
		];
	}
}
