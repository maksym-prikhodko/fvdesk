<?php
namespace App\Http\Controllers\Agent;
use App\Http\Controllers\Controller;
use App\Http\Requests\OrganizationRequest;
use App\Http\Requests\OrganizationUpdate;
use App\Model\Agent_panel\Organization;
class OrganizationController extends Controller {
	public function __construct() {
		$this->middleware('auth');
		$this->middleware('role.agent');
		$this->middleware('roles');
	}
	public function index(Organization $org) {
		try {
			$orgs = $org->get();
			return view('themes.default1.agent.organization.index', compact('orgs'));
		} catch (Exception $e) {
			return view('404');
		}
	}
	public function create() {
		try {
			return view('themes.default1.agent.organization.create');
		} catch (Exception $e) {
			return view('404');
		}
	}
	public function store(Organization $org, OrganizationRequest $request) {
		try {
			if ($org->fill($request->input())->save() == true) {
				return redirect('organizations')->with('success', 'Organization  Created Successfully');
			} else {
				return redirect('organizations')->with('fails', 'Organization can not Create');
			}
		} catch (Exception $e) {
			return redirect('organizations')->with('fails', 'Organization can not Create');
		}
	}
	public function show($id, Organization $org) {
		try {
			$orgs = $org->whereId($id)->first();
			return view('themes.default1.agent.organization.show', compact('orgs'));
		} catch (Exception $e) {
			return view('404');
		}
	}
	public function edit($id, Organization $org) {
		try {
			$orgs = $org->whereId($id)->first();
			return view('themes.default1.agent.organization.edit', compact('orgs'));
		} catch (Exception $e) {
			return view('404');
		}
	}
	public function update($id, Organization $org, OrganizationUpdate $request) {
		try {
			$orgs = $org->whereId($id)->first();
			if ($orgs->fill($request->input())->save() == true) {
				return redirect('organizations')->with('success', 'Organization  Updated Successfully');
			} else {
				return redirect('organizations')->with('fails', 'Organization  can not Update');
			}
		} catch (Exception $e) {
			return redirect('organizations')->with('fails', 'Organization  can not Update');
		}
	}
	public function destroy($id) {
		try {
			$orgs = $org->whereId($id)->first();
			if ($orgs->delete() == true) {
				return redirect('organizations')->with('success', 'Organization  Deleted Successfully');
			} else {
				return redirect('organizations')->with('fails', 'Organization  can not Delete');
			}
		} catch (Exception $e) {
			return redirect('organizations')->with('fails', 'Organization  can not Delete');
		}
	}
}
