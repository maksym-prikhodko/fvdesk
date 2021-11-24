<?php namespace App\Http\Requests;
use App\Http\Requests\Request;
class TemplateRequest extends Request {
	public function authorize() {
		return true;
	}
	public function rules() {
		return [
			'name' => 'required|unique:template',
			'ban_status' => 'required',
			'template_set_to_clone' => 'required',
			'language' => 'required',
		];
	}
}
