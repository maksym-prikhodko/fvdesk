<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Http\Requests\FormRequest;
use App\Model\Manage\Forms;
use App\Model\Utility\Form_type;
use App\Model\Utility\Form_visibility;
class FormController extends Controller {
	public function __construct() {
		$this->middleware('auth');
		$this->middleware('roles');
	}
	public function index(Forms $form) {
		try {
			$forms = $form->get();
			return view('themes.default1.admin.manage.form.index', compact('forms'));
		} catch (Exception $e) {
			return view('404');
		}
	}
	public function create(Form_visibility $visibility, Form_type $type) {
		try {
			return view('themes.default1.admin.manage.form.create', compact('visibility', 'type'));
		} catch (Exception $e) {
			return view('404');
		}
	}
	public function store(Forms $form, FormRequest $request) {
		try {
			if ($form->fill($request->input())->save() == true) {
				return redirect('form')->with('success', 'Form Created Successfully');
			} else {
				return redirect('form')->with('fails', 'Form can not Create');
			}
		} catch (Exception $e) {
			return redirect('form')->with('fails', 'Form can not Create');
		}
	}
	public function show($id) {
	}
	public function edit($id, Forms $form, Form_visibility $visibility, Form_type $type) {
		try {
			$forms = $form->whereId($id)->first();
			return view('themes.default1.admin.manage.form.edit', compact('forms', 'visibility', 'type'));
		} catch (Exception $e) {
			return view('404');
		}
	}
	public function update($id, Forms $form, FormRequest $request) {
		try {
			$forms = $form->whereId($id)->first();
			if ($forms->fill($request->input())->save() == true) {
				return redirect('form')->with('success', 'Form Updated Successfully');
			} else {
				return redirect('form')->with('fails', 'Form can not Update');
			}
		} catch (Exception $e) {
			return redirect('form')->with('fails', 'Form can not Create');
		}
	}
	public function destroy($id, Forms $form) {
		try {
			$forms = $form->whereId($id)->first();
			if ($forms->delete() == true) {
				return redirect('form')->with('success', 'Form Deleted Successfully');
			} else {
				return redirect('form')->with('fails', 'Form can not Deleted');
			}
		} catch (Exception $e) {
			return redirect('form')->with('fails', 'Form can not Create');
		}
	}
}
