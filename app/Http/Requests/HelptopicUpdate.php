<?php namespace App\Http\Requests;
use App\Http\Requests\Request;
class HelptopicUpdate extends Request {
	public function authorize()
	{
		return true;
	}
	public function rules()
	{
		return [
			'parent_topic'		=>		'required',
			'custom_form'		=>		'required',
			'department'		=>		'required',
			'priority'			=>		'required',
			'sla_plan'			=>		'required',
			'auto_assign'		=>		'required'
		];
	}
}
