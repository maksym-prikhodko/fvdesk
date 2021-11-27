<?php
namespace App\Http\Requests;
use App\Http\Requests\Request;
class FormRequest extends Request {
	public function authorize() {
		return true;
	}
	public function rules() {
		return [
			'title' => 'required',
			'label' => 'required',
			'type' => 'required',
			'visibility' => 'required',
		];
	}
}
