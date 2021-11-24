<?php namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Http\Requests\AgentRequest;
use App\Http\Requests\AgentUpdate;
use Illuminate\Http\Request;
use App\Model\Agent\Agents;
use App\Model\Utility\Timezones;
use App\Model\Agent\Groups;
use App\Model\Agent\Department;
use App\Model\Agent\Teams;
use App\Model\Agent\Assign_team_agent;
use DB;
use App\User;
use Auth;
class AgentController extends Controller {
	public function __construct()
	{
		$this->middleware('auth');
		$this->middleware('roles');
	}
	public function index(User $user)
	{
		try
		{
			$user = $user->where('role','agent')->get();
			return view('themes.default1.admin.agent.agents.index', compact('user'));
		}
		catch(Exception $e)
		{
			return view('404');
		}
	}
	public function create(Assign_team_agent $team_assign_agent, Timezones $timezone, Groups $group, Department $department, Teams $team)
	{
		try
		{
			$team= $team->get();
			$timezones = $timezone->get();
			$groups = $group->get();
			$departments = $department->get();
				$teams = $team->lists('id','name');
			return view('themes.default1.admin.agent.agents.create', compact('assign','teams','agents','timezones','groups','departments','team'));
		}
		catch(Exception $e)
		{
			return view('404');
		}
	}
	public function store(User $user, AgentRequest $request, Assign_team_agent $team_assign_agent)
	{
		try
		{
			$user->role = 'agent';
			$user->fill($request->input())->save();
				$requests = $request->input('team_id');
				$id = $user->id;
				foreach($requests as $req)
					{
						 DB::insert('insert into team_assign_agent (team_id, agent_id) values (?,?)', [$req, $id]); 
					}
			if($user->save()==true)
			{
				return redirect('agents')->with('success','Agent Created sucessfully');
			}
			else
			{
				return redirect('agents')->with('fails','Agent can not Create');
			}
		}
		catch( Exception $e)
		{
			return redirect('agents')->with('fails','Agent can not Create');
		}
	}
	public function show($id)
	{
	}
	public function edit($id,User $user, Assign_team_agent $team_assign_agent, Timezones $timezone, Groups $group, Department $department, Teams $team)
	{
		try
		{
			$user = $user->whereId($id)->first();
			$team= $team->get();
			$teams1 = $team->lists('name','id');
			$timezones = $timezone->get();
			$groups = $group->get();
			$departments = $department->get();
			$table = $team_assign_agent->where('agent_id',$id)->first();
			$teams = $team->lists('id','name');
			$assign = $team_assign_agent->where('agent_id',$id)->lists('team_id');
			return view('themes.default1.admin.agent.agents.edit', compact('teams','assign','table','teams1','selectedTeams','user','timezones','groups','departments','team','exp','counted'));
		}
		catch(Exception $e)
		{
			return redirect('agents')->with('fail','No such file');
		}
	}
	public function update($id, User $user, AgentUpdate $request, Assign_team_agent $team_assign_agent)
	{
		try
		{
			$user = $user->whereId($id)->first();
			$daylight_save=$request->input('daylight_save');
			$limit_access=$request->input('limit_access');
			$directory_listing=$request->input('directory_listing');
			$vocation_mode=$request->input('vocation_mode');
			$user->daylight_save=$daylight_save;
			$user->limit_access=$limit_access;
			$user->directory_listing=$directory_listing;
			$user->vocation_mode=$vocation_mode;
			$table = $team_assign_agent->where('agent_id',$id);
			$table->delete();
			$requests = $request->input('team_id');
			foreach($requests as $req)
				{
					 DB::insert('insert into team_assign_agent (team_id, agent_id) values (?,?)', [$req, $id]); 
				}
			$user->fill($request->except('daylight_save','limit_access','directory_listing','vocation_mode','assign_team'))->save();
			return redirect('agents')->with('success','Agent Updated sucessfully');
		}
		catch (Exception $e)
		{
			return redirect('agents')->with('fails','Agent did not update');
		}
	}
	public function destroy($id, User $user, Assign_team_agent $team_assign_agent)
	{
		try
		{
			$team_assign_agent = $team_assign_agent->where('agent_id',$id);
			$team_assign_agent->delete();
			$user = $user->whereId($id)->first();
			if($user->delete())
			{
				return redirect('agents')->with('success','Agent Deleted sucessfully');
			}
			else
			{
				return redirect('agents')->with('fails','Agent can not  Delete ');	
			}
		}
		catch(Exception $e)
		{
			return redirect('agents')->with('fails','Agent can not  Delete if the team Excist');
		}
	}
}
