<?php
namespace App\Http\Requests;
use App\Http\Requests\Request;
class AgentUpdate extends Request {
	public function authorize() {
		return true;
	}
	public function rules() {
		return [
			'first_name' => 'required',
			'last_name' => 'required',
			'email' => 'required|email',
			'account_type' => 'required',
			'role' => 'required',
			'assign_group' => 'required',
			'primary_dpt' => 'required',
			'agent_tzone' => 'required',
			'phone_number' => 'phone:IN',
			'mobile' => 'phone:IN',
			'team_id' => 'required',
		];
	}
}
