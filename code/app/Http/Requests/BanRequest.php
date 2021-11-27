<?php
namespace App\Http\Requests;
use App\Http\Requests\Request;
class BanRequest extends Request {
	public function authorize() {
		return true;
	}
	public function rules() {
		return [
			'email_address' => 'required|email|unique:banlist',
			'ban_status' => 'required',
		];
	}
}
