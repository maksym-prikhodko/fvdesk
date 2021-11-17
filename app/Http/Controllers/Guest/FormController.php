<?php namespace App\Http\Controllers\Guest;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Model\Manage\Help_topic;
use App\Model\Form\Form_name;
use App\Model\Form\Form_details;
use App\Model\Form\Form_value;
use App\User;
use Form;
use Input;
use DB;
use App\Http\Requests\TicketForm;
class FormController extends Controller {
	public function getForm(Form_name $name, Form_details $details, Help_topic $topics)
	{
		$name = $name->where('status',1)->get();
		$ids = $name->where('id',2);
		foreach($ids as $i)
		{
			$id = $i->id;
		}
		$detail_form_name_id = $details->where('form_name_id',$id)->get();
		$count = count($detail_form_name_id);
		return view('themes.default1.client.guest-user.form',compact('name','detail_form_name_id','topics'));
	}
	public function postForm(Form_name $name, Form_details $details)
	{
		 $name = $name->where('status',1)->get();
		$ids = $name->where('id',2);
		foreach($ids as $i)
		{
			$id = $i->id;
		}
		 $field=$details->where('form_name_id',$id)->get();
		 $var=" ";
		 foreach ($field as $key) {
		 $type=$key->type; 
		 $label=$key->label; 
		 $var.=",".$type."-".$label;
		 }
 			return $var;
	}
	public function postedForm(Request $request, User $user)
	{
		$user->name = $request->input('Name');
		$user->email = $request->input('Email');
		$user->save();	
	}
}
