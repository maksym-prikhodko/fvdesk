<?php
namespace App\Http\Requests;
use App\Http\Requests\Request;
class AgentRequest extends Request {
	public function authorize() {
		return true;
	}
	public function rules() {
		return [
			'user_name' => 'required|unique:agents',
			'first_name' => 'required',
			'last_name' => 'required',
			'email' => 'required',
			'account_type' => 'required',
			'assign_group' => 'required',
			'primary_dpt' => 'required',
			'agent_tzone' => 'required',
			'phone_number' => 'phone:IN',
			'mobile' => 'phone:IN',
			'team_id' => 'required',
		];
	}
}
