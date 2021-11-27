<?php
namespace App\Http\Requests;
use App\Http\Requests\Request;
class OrganizationRequest extends Request {
	public function authorize() {
		return true;
	}
	public function rules() {
		return [
			'name' => 'required|unique:organization',
			'website' => 'url',
			'phone' => 'size:10',
		];
	}
}
