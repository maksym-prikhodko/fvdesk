<?php
namespace App\Http\Requests;
use App\Http\Requests\Request;
class BanlistRequest extends Request {
	public function authorize() {
		return true;
	}
	public function rules() {
		return [
			'email_address' => 'email',
			'ban_status' => 'required',
		];
	}
}
