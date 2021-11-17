<?php namespace App\Http\Controllers\Admin;
use App\Http\Requests\DepartmentRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\DepartmentUpdate;
use App\Model\Manage\Sla_plan;
use App\Model\Agent\Agents;
use App\Model\Email\Emails;
use App\Model\Agent\Groups;
use App\Model\Agent\Department;
use App\Model\Email\Template;
use App\Model\Agent\Teams;
use App\Model\Agent\Group_assign_department;
use DB;
use App\User;
class DepartmentController extends Controller {
	public function __construct()
	{
		$this->middleware('auth');
		$this->middleware('roles');
	}
	public function index(Department $department)
	{
		try
		{
			$departments = $department->get();
			return view('themes.default1.admin.agent.departments.index',compact('departments'));
		}
		catch(Exception $e)
		{
			return view('404');
		}
	}
	public function create(User $user,Group_assign_department $group_assign_department, Department $department, Sla_plan $sla,Template $template,Emails $email,Groups $group)
	{
		try
		{
			$slas=$sla->get();
			$user=$user->where('role','agent')->get();
			$emails=$email->get();
			$templates = $template->get();
			$department = $department->get();
			$groups = $group->lists('id','name');
			return view('themes.default1.admin.agent.departments.create',compact('department','templates','slas','user','emails','groups'));
		}
		catch(Exception $e)
		{
			return view('404');
		}
	}
	public function store(Department $department,DepartmentRequest $request)
	{
		try
		{
				$department -> fill($request->except('group_id'))->save();
				$requests = $request->input('group_id');
				$id = $department->id;
				foreach($requests as $req)
					{
						 DB::insert('insert into group_assign_department(group_id, department_id) values (?,?)', [$req, $id]); 
					}
			if($department->save()==true)
			{
				return redirect('departments')->with('success','Department Created sucessfully');
			}
			else
			{
				return redirect('departments')->with('fails','Department can not Create');
			}
		}
		catch(Exception $e)
		{
			return redirect('departments')->with('fails','Department can not Create');
		}
	}
	public function show($id)
	{
	}
	public function edit($id,User $user, Group_assign_department $group_assign_department, Template $template, Teams $team, Department $department,Sla_plan $sla,Emails $email,Groups $group)
	{
		try
		{
			$slas=$sla->get();
			$user=$user->where('role','agent')->get();
			$emails=$email->get();
			$templates = $template->get();
			$departments = $department->whereId($id)->first();
			$groups = $group->lists('id','name');
			$assign = $group_assign_department->where('department_id',$id)->lists('group_id');
			return view('themes.default1.admin.agent.departments.edit',compact('assign','team','templates','departments','slas','user','emails','groups'));
		}
		catch(Exception $e)
		{
			return view('404');
		}
	}
	public function update($id,Group_assign_department $group_assign_department, Department $department, DepartmentUpdate $request)
	{	
		try
		{
			$table = $group_assign_department->where('department_id',$id);
			$table->delete();
			$requests = $request->input('group_id');
			foreach($requests as $req)
				{
					 DB::insert('insert into group_assign_department (group_id, department_id) values (?,?)', [$req, $id]); 
				}
			$departments = $department->whereId($id)->first();
			if($departments->fill($request->except('group_access'))->save())
			{
				return redirect('departments')->with('success','Department Updated sucessfully');
			}
			else
			{
				return redirect('departments')->with('fails','Department not Updated');
			}
		}
		catch(Exception $e)
		{
			return redirect('departments')->with('fails','Department not Updated');
		}
	}
	public function destroy($id, Department $department, Group_assign_department $group_assign_department)
	{
		try
		{
			$group_assign_department = $group_assign_department->where('department_id',$id);
			$group_assign_department->delete();
			$departments = $department->whereId($id)->first();
			if($departments->delete()==true)
			{
				return redirect('departments')->with('success','Department Deleted sucessfully');
			}
			else
			{
				return redirect('departments')->with('fails','Department can not Delete');
			}
		}
		catch(Exception $e)
		{
			return redirect('departments')->with('fails','Department can not Delete');
		}
	}
}
