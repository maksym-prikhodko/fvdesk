<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Http\Requests\TeamRequest;
use App\Http\Requests\TeamUpdate;
use App\Model\Agent\Assign_team_agent;
use App\Model\Agent\Teams;
use App\User;
class TeamController extends Controller {
	public function __construct() {
		$this->middleware('auth');
		$this->middleware('roles');
	}
	public function index(Teams $team, Assign_team_agent $assign_team_agent) {
		try {
			$teams = $team->get();
			$id = $teams->lists('id');
			$assign_team_agent = $assign_team_agent->get();
			return view('themes.default1.admin.agent.teams.index', compact('assign_team_agent', 'teams'));
		} catch (Exception $e) {
			return view('404');
		}
	}
	public function create(User $user) {
		try {
			$user = $user->get();
			return view('themes.default1.admin.agent.teams.create', compact('user'));
		} catch (Exception $e) {
			return view('404');
		}
	}
	public function store(Teams $team, TeamRequest $request) {
		try {
			if ($team->fill($request->input())->save() == true) {
				return redirect('teams')->with('success', 'Teams  Created Successfully');
			} else {
				return redirect('teams')->with('fails', 'Teams can not Create');
			}
		} catch (Exception $e) {
			return redirect('teams')->with('fails', 'Teams can not Create');
		}
	}
	public function show($id) {
	}
	public function edit($id, User $user, Assign_team_agent $assign_team_agent, Teams $team) {
		try {
			$user = $user->whereId($id)->first();
			$teams = $team->whereId($id)->first();
			$agent_team = $assign_team_agent->where('team_id', $id)->get();
			$agent_id = $agent_team->lists('agent_id', 'agent_id');
			return view('themes.default1.admin.agent.teams.edit', compact('agent_id', 'user', 'teams', 'allagents'));
		} catch (Exception $e) {
			return view('404');
		}
	}
	public function update($id, Teams $team, TeamUpdate $request) {
		try {
			$teams = $team->whereId($id)->first();
			$alert = $request->input('assign_alert');
			$teams->assign_alert = $alert;
			$teams->save(); 
			if ($teams->fill($request->input())->save() == true) {
				return redirect('teams')->with('success', 'Teams  Updated Successfully');
			} else {
				return redirect('teams')->with('fails', 'Teams  can not Update');
			}
		} catch (Exception $e) {
			return redirect('teams')->with('fails', 'Teams  can not Update');
		}
	}
	public function destroy($id, Teams $team, Assign_team_agent $assign_team_agent) {
		try {
			$assign_team_agent->where('team_id', $id)->delete();
			$teams = $team->whereId($id)->first();
			if ($teams->delete() == true) {
				return redirect('teams')->with('success', 'Teams  Deleted Successfully');
			} else {
				return redirect('teams')->with('fails', 'Teams can not Delete');
			}
		} catch (Exception $e) {
			return redirect('teams')->with('fails', 'Teams can not Delete');
		}
	}
}
