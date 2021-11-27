<?php
namespace App\Http\Requests;
use App\Http\Requests\Request;
class SlaUpdate extends Request {
	public function authorize() {
		return true;
	}
	public function rules() {
		return [
			'grace_period' => 'required',
		];
	}
}
