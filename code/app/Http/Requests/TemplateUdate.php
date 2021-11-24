<?php namespace App\Http\Requests;
use App\Http\Requests\Request;
class TemplateUdate extends Request {
	public function authorize() {
		return true;
	}
	public function rules() {
		return [
			'ban_status' => 'required',
			'template_set_to_clone' => 'required',
			'language' => 'required',
		];
	}
}
