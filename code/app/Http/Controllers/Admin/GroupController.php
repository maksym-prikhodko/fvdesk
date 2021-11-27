<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Http\Requests\GroupRequest;
use App\Model\Agent\Department;
use App\Model\Agent\Groups;
use App\Model\Agent\Group_assign_department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
class GroupController extends Controller {
	public function __construct() {
		$this->middleware('auth');
		$this->middleware('roles');
	}
	public function index(Groups $group, Department $department, Group_assign_department $group_assign_department) {
		try {
			$groups = $group->get();
			$departments = $department->lists('id');
			return view('themes.default1.admin.agent.groups.index', compact('departments', 'group_assign_department', 'groups'));
		} catch (Exception $e) {
			return view('404');
		}
	}
	public function create() {
		try {
			return view('themes.default1.admin.agent.groups.create');
		} catch (Exception $e) {
			return view('404');
		}
	}
	public function store(Groups $group, GroupRequest $request) {
		try {
			if ($group->fill($request->input())->save() == true) {
				return redirect('groups')->with('success', 'Groups Created Successfully');
			} else {
				return redirect('groups')->with('fails', 'Groups can not Create');
			}
		} catch (Exception $e) {
			return redirect('groups')->with('fails', 'Groups can not Create');
		}
	}
	public function show($id, Groups $group, Request $request) {
	}
	public function edit($id, Groups $group) {
		try {
			$groups = $group->whereId($id)->first();
			return view('themes.default1.admin.agent.groups.edit', compact('groups'));
		} catch (Exception $e) {
			return view('404');
		}
	}
	public function update($id, Groups $group, Request $request) {
		try {
			$var = $group->whereId($id)->first();
			$status = $request->Input('group_status');
			$var->group_status = $status;
			$createTicket = $request->Input('can_create_ticket');
			$var->can_create_ticket = $createTicket;
			$editTicket = $request->Input('can_edit_ticket');
			$var->can_edit_ticket = $editTicket;
			$postTicket = $request->Input('can_post_ticket');
			$var->can_post_ticket = $postTicket;
			$closeTicket = $request->Input('can_close_ticket');
			$var->can_close_ticket = $closeTicket;
			$assignTicket = $request->Input('can_assign_ticket');
			$var->can_assign_ticket = $assignTicket;
			$trasferTicket = $request->Input('can_trasfer_ticket');
			$var->can_trasfer_ticket = $trasferTicket;
			$deleteTicket = $request->Input('can_delete_ticket');
			$var->can_delete_ticket = $deleteTicket;
			$banEmail = $request->Input('can_ban_email');
			$var->can_ban_email = $banEmail;
			$manageCanned = $request->Input('can_manage_canned');
			$var->can_manage_canned = $manageCanned;
			$manageFaq = $request->Input('can_manage_faq');
			$var->can_manage_faq = $manageFaq;
			$viewAgentStats = $request->Input('can_view_agent_stats');
			$var->can_view_agent_stats = $viewAgentStats;
			$departmentAccess = $request->Input('department_access');
			$var->department_access = $departmentAccess;
			$adminNotes = $request->Input('admin_notes');
			$var->admin_notes = $adminNotes;
			if ($var->save() == true) {
				return redirect('groups')->with('success', 'Group Updated Successfully');
			} else {
				return redirect('groups')->with('fails', 'Group can not Update');
			}
		} catch (Exception $e) {
			return redirect('groups')->with('fails', 'Groups can not Create');
		}
	}
	public function destroy($id, Groups $group, Group_assign_department $group_assign_department) {
		try {
			$group_assign_department->where('group_id', $id)->delete();
			$groups = $group->whereId($id)->first();
			if ($groups->delete() == true) {
				return redirect('groups')->with('success', 'Group Deleted Successfully');
			} else {
				return redirect('groups')->with('fails', 'Group can not Delete');
			}
		} catch (Exception $e) {
			return redirect('groups')->with('fails', 'Groups can not Create');
		}
	}
}
