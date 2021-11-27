<?php
namespace App\Http\Requests;
use App\Http\Requests\Request;
class CompanyRequest extends Request {
	public function authorize() {
		return true;
	}
	public function rules() {
		return [
			'company_name' => 'required',
			'website' => 'url',
			'phone' => 'numeric',
			'logo' => 'image',
		];
	}
}
