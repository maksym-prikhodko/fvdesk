<?php namespace App\Http\Controllers\Admin;
use App\Http\Requests;
use App\Http\Requests\HelptopicRequest;
use App\Http\Requests\HelptopicUpdate;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Model\Agent\Department;
use App\Model\Manage\Help_topic;
use App\Model\Agent\Agents;
use App\Model\Manage\Sla_plan;
use App\Model\Form\Form_name;
use App\Model\Utility\Priority;
class HelptopicController extends Controller {
	public function __construct()
	{
		$this->middleware('auth');
		$this->middleware('roles');
	}
	public function index(Help_topic $topic)
	{
		try
		{
			$topics = $topic->get();
			return view('themes.default1.admin.manage.helptopic.index',compact('topics'));
		}
		catch(Exception $e)
		{
			return view('404');
		}
	}
	public function create(Priority $priority,Department $department, Help_topic $topic, Form_name $form, Agents $agent, Sla_plan $sla)
	{
		try
		{
			$departments = $department->get();
			$topics = $topic->get();
			$forms = $form->get();
			$agents = $agent->get();
			$slas = $sla->get();
			$priority = $priority->get();
			return view('themes.default1.admin.manage.helptopic.create',compact('priority','departments','topics','forms','agents','slas'));
		}
		catch(Exception $e)
		{
			return view('404');
		}
	}
	public function store(Help_topic $topic, HelptopicRequest $request)
	{
		try
		{
			if($topic->fill($request->input())->save()==true)
			{
				return redirect('helptopic')->with('success','Helptopic Created Successfully');
			}
			else
			{
				return redirect('helptopic')->with('fails','Helptopic can not Create');	
			}
		}
		catch(Exception $e)
		{
			return redirect('helptopic')->with('fails','Helptopic can not Create');	
		}
	}
	public function show($id)
	{
	}
	public function edit($id,Priority $priority,Department $department, Help_topic $topic, Form_name $form, Agents $agent, Sla_plan $sla)
	{
		try
		{
			$departments = $department->get();
			$topics = $topic->whereId($id)->first();
			$forms = $form->get();
			$agents = $agent->get();
			$slas = $sla->get();
			$priority = $priority->get();
			return view('themes.default1.admin.manage.helptopic.edit',compact('priority','departments','topics','forms','agents','slas'));
		}
		catch(Exception $e)
		{
			return view('404');
		}
	}
	public function update($id, Help_topic $topic, HelptopicUpdate $request)
	{
		try
		{
			$topics = $topic->whereId($id)->first();
			if($topics->fill($request->input())->save()==true)
			{
				return redirect('helptopic')->with('success','Helptopic Updated Successfully');
			}
			else
			{
				return redirect('helptopic')->with('fails','Helptopic can not Updated');	
			}
		}
		catch(Exception $e)
		{
			return redirect('helptopic')->with('fails','Helptopic can not Create');
		}
	}
	public function destroy($id, Help_topic $topic)
	{
		try
		{
			$topics = $topic->whereId($id)->first();
			if($topics->delete()==true)
			{
				return redirect('helptopic')->with('success','Helptopic Deleted Successfully');
			}
			else
			{
				return redirect('helptopic')->with('fails','Helptopic can not Delete');	
			}
		}
		catch(Exception $e)
		{
			return redirect('helptopic')->with('fails','Helptopic can not Create');
		}
	}
}
