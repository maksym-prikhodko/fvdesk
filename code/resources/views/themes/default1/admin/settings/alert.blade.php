@extends('themes.default1.layouts.blank')
@section('Settings')
class="active"
@stop
@section('settings-bar')
active
@stop
@section('alert')
class="active"
@stop
@section('HeadInclude')
@stop
@section('PageHeader')
@stop
@section('breadcrumbs')
<ol class="breadcrumb">
</ol>
@stop
@section('content')
	{!! Form::model($alerts,['url' => 'postalert/'.$alerts->id, 'method' => 'PATCH']) !!}
		<div class="box box-primary">
	<div class="content-header">
	 	<h4>{{Lang::get('lang.alert_notices')}}	{!! Form::submit(Lang::get('lang.save'),['class'=>'form-group btn btn-primary pull-right'])!!}</h4>
	</div>
@if(Session::has('success'))
    <div class="alert alert-success alert-dismissable">
        <i class="fa  fa-check-circle"></i>
        <b>Success!</b>
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        {{Session::get('success')}}
    </div>
    @endif
    @if(Session::has('fails'))
    <div class="alert alert-danger alert-dismissable">
        <i class="fa fa-ban"></i>
        <b>Alert!</b> Failed.
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        {{Session::get('fails')}}
    </div>
    @endif
	<div class="box-body">
	<div class="row">
	<div class="col-md-12">
          <div class="row">
            <div class="col-md-6">
              <div class="box box-primary">
                <div class="box-header">
                  <h3 class="box-title">{{Lang::get('lang.new_ticket_alert')}}</h3>
                  <div class="pull-right">
              		</div>
                </div>
                  <div class="box-body">
                    <div class="form-group">
                      {!! Form::checkbox('ticket_status',1) !!}
					{!! Form::label('ticket_status',Lang::get('lang.status')) !!}
                    </div>
                    <div class="form-group">
                      {!! Form::checkbox('ticket_admin_email',1) !!}
				     {!! Form::label('ticket_admin_email',Lang::get('lang.admin_email')) !!}
                    </div>
                    <div class="form-group">
                    {!! Form::checkbox('ticket_department_manager',1) !!}
					{!! Form::label('ticket_department_manager',Lang::get('lang.department_manager')) !!}
                    </div>
                    <div class="form-group">
                    {!! Form::checkbox('ticket_department_member',1) !!}
					{!! Form::label('ticket_department_member',Lang::get('lang.department_members')) !!}
                    </div>
                    <div class="form-group">
                    {!! Form::checkbox('ticket_organization_accmanager',1) !!}
					{!! Form::label('ticket_organization_accmanager',Lang::get('lang.organization_account_manager')) !!}
                    </div>
                  </div>
              </div>
              <div class="box box-primary">
                <div class="box-header">
                  <h3 class="box-title">{{Lang::get('lang.new_message_alert')}}</h3>
                  <div class="pull-right">
              		</div>
                </div>
                  <div class="box-body">
                    <div class="form-group">
                    {!! Form::checkbox('message_status',1) !!}
					{!! Form::label('message_status',Lang::get('lang.status')) !!}
                    </div>
                    <div class="form-group">
                    {!! Form::checkbox('message_last_responder',1) !!}
					{!! Form::label('message_last_responder',Lang::get('lang.last_respondent')) !!}
                    </div>
                    <div class="form-group">
                    {!! Form::checkbox('message_assigned_agent',1) !!}
					{!! Form::label('message_assigned_agent',Lang::get('lang.assigned_agent_team')) !!}
                    </div>
			 		<div class="form-group">
					{!! Form::checkbox('message_department_manager',1) !!}
					{!! Form::label('message_department_manager',Lang::get('lang.department_manager')) !!}
					</div>
					<div class="form-group">
				    {!! Form::checkbox('message_organization_accmanager',1) !!}
					{!! Form::label('message_organization_accmanager',Lang::get('lang.organization_account_manager')) !!}
					</div>
                  </div>
              </div>
              <div class="box box-primary">
                <div class="box-header">
                  <h3 class="box-title">{{Lang::get('lang.new_internal_note_alert')}}</h3>
                  <div class="pull-right">
              		</div>
                </div>
                  <div class="box-body">
			 	<div class="form-group">
				{!! Form::checkbox('transfer_status',1) !!}
					{!! Form::label('transfer_status',Lang::get('lang.status')) !!}
				</div>
			 	<div class="form-group">
				{!! Form::checkbox('transfer_assigned_agent',1) !!}
					{!! Form::label('transfer_assigned_agent',Lang::get('lang.ticket_assignment_alert')) !!}
				</div>
			 	<div class="form-group">
				{!! Form::checkbox('transfer_department_manager',1) !!}
					{!! Form::label('transfer_department_manager',Lang::get('lang.department_manager')) !!}
				</div>
			 	<div class="form-group">
				{!! Form::checkbox('transfer_department_member',1) !!}
					{!! Form::label('transfer_department_member',Lang::get('lang.department_members')) !!}
				</div>
                  </div>
              </div>
              <div class="box box-primary">
                <div class="box-header">
                  <h3 class="box-title">{{Lang::get('lang.system_alerts')}}</h3>
                  <div class="pull-right">
              		</div>
                </div>
                  <div class="box-body">
			 	<div class="form-group">
				{!! Form::checkbox('system_error',1) !!}
					{!! Form::label('system_error',Lang::get('lang.system_errors')) !!}
				</div>
			 	<div class="form-group">
				{!! Form::checkbox('sql_error',1) !!}
					{!! Form::label('sql_error',Lang::get('lang.SQL_errors')) !!}
				</div>
			 	<div class="form-group">
				{!! Form::checkbox('excessive_failure',1) !!}
					{!! Form::label('excessive_failure',Lang::get('lang.excessive_failed_login_attempts')) !!}
				</div>
                  </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="box box-primary">
                <div class="box-header">
                  <h3 class="box-title">{{Lang::get('lang.overdue_ticket_alert')}}</h3>
                  <div class="pull-right">
              		</div>
                </div>
                  <div class="box-body">
                    <div class="form-group">
					{!! Form::checkbox('overdue_status',1) !!}
					{!! Form::label('overdue_status',Lang::get('lang.status')) !!}
					</div>
			 	<div class="form-group">
				{!! Form::checkbox('overdue_assigned_agent',1) !!}
					{!! Form::label('overdue_assigned_agent',Lang::get('lang.assigned_agent_team')) !!}
				</div>
			 	<div class="form-group">
				{!! Form::checkbox('overdue_department_manager',1) !!}
					{!! Form::label('overdue_department_manager',Lang::get('lang.department_manager')) !!}
				</div>
			 	<div class="form-group">
				{!! Form::checkbox('overdue_department_member',1) !!}
				{!! Form::label('overdue_department_member',Lang::get('lang.department_members')) !!}
				</div>
                  </div>
              </div>
              <div class="box box-primary">
                <div class="box-header">
                  <h3 class="box-title">{{Lang::get('lang.new_internal_note_alert')}}</h3>
                  <div class="pull-right">
              		</div>
                </div>
                  <div class="box-body">
                    <div class="form-group">
				{!! Form::checkbox('internal_status',1) !!}
					{!! Form::label('internal_status',Lang::get('lang.status')) !!}
				</div>
			 	<div class="form-group">
				{!! Form::checkbox('internal_last_responder',1) !!}
					{!! Form::label('internal_last_responder',Lang::get('lang.last_respondent')) !!}
				</div>
			 	<div class="form-group">
				{!! Form::checkbox('internal_assigned_agent',1) !!}
					{!! Form::label('internal_assigned_agent',Lang::get('lang.assigned_agent_team')) !!}
				</div>
			 	<div class="form-group">
				{!! Form::checkbox('internal_department_manager',1) !!}
					{!! Form::label('internal_department_manager',Lang::get('lang.department_manager')) !!}
				</div>
                  </div>
              </div>
              <div class="box box-primary">
                <div class="box-header">
                  <h3 class="box-title">{{Lang::get('lang.ticket_assignment_alert')}}</h3>
                  <div class="pull-right">
              		</div>
                </div>
                  <div class="box-body">
			 	<div class="form-group">
				{!! Form::checkbox('assignment_status',1) !!}
					{!! Form::label('assignment_status',Lang::get('lang.status')) !!}
				</div>
			 	<div class="form-group">
				{!! Form::checkbox('assignment_assigned_agent',1) !!}
					{!! Form::label('assignment_assigned_agent',Lang::get('lang.assigned_agent_team')) !!}
				</div>
			 	<div class="form-group">
				{!! Form::checkbox('assignment_team_leader',1) !!}
					{!! Form::label('assignment_team_leader',Lang::get('lang.team_lead')) !!}
				</div>
			 	<div class="form-group">
				{!! Form::checkbox('assignment_team_member',1) !!}
					{!! Form::label('assignment_team_member',Lang::get('lang.team_members')) !!}
				</div>
                  </div>
              </div>
            </div>
            </div>
    </div>
            </section>
@stop
</div>
@section('FooterInclude')
@stop
@stop
