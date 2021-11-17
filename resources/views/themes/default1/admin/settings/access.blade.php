@extends('themes.default1.layouts.blank')
@section('Settings')
class="active"
@stop
@section('settings-bar')
active
@stop
@section('access')
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
	{!! Form::model($accesses,['url' => 'postaccess/'.$accesses->id, 'method' => 'PATCH']) !!}
	<div class="row">
		<div class="col-md-12">
			<div class="box box-primary">
				<div class="box-header">
                <h3 class="box-title">{{Lang::get('lang.access')}}</h3> <div class="pull-right">
                {!! Form::submit(Lang::get('lang.save'),['class'=>'btn btn-primary'])!!}
              </div>
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
          <div class="box-body table-responsive"style="overflow:hidden;">
             <div class="row">
               <div class="col-md-4">
               <div class="form-group">
                {!! Form::label('password_expire',Lang::get('lang.expiration_policy')) !!}
				{!!Form::select('password_expire',['1 month','2 month','3 month'],null,['class' => 'form-control select']) !!}
			</div>
		</div>
			<div class="col-md-4">
             <div class="form-group">
				{!! Form::label('reset_ticket_expire',Lang::get('lang.reset_token_expiration')) !!}
				{!! Form::text('reset_ticket_expire',$accesses->reset_ticket_expire,['class' => 'form-control']) !!}
			</div>
			</div>
			<div class="col-md-4">
			    <div class="form-group">
				{!! Form::label('agent_session',Lang::get('lang.agent_session_timeout')) !!}
				{!! Form::text('agent_session',$accesses->agent_session,['class' => 'form-control']) !!}
			</div>
			</div>
			</div>
			<div class="row">
			<div class="col-md-4">
				<div class="form-group">
				{!! Form::label('password_reset',Lang::get('lang.allow_password_resets')) !!}
				{!! Form::checkbox('password_reset',1) !!}
			</div>
			</div>
            </div>
			<div class="row">
			<div class="col-md-6">
                <div class="form-group">
				{!! Form::label('reg_method',Lang::get('lang.registration_method')) !!}
				{!!Form::select('reg_method',['public','private','dissabled'],null,['class' => 'form-control select']) !!}
			</div>
			</div>
			<div class="col-md-6">
                 <div class="form-group">
				{!! Form::label('user_session',Lang::get('lang.user_session_timeout')) !!}
				{!! Form::text('user_session',$accesses->user_session,['class' => 'form-control']) !!}
			</div>
			</div>
			</div>
			<div class="row">
			<div class="col-md-4">
				<div class="form-group">
				{!! Form::checkbox('bind_agent_ip',1,true) !!} &nbsp;
				{!! Form::label('bind_agent_ip',Lang::get('lang.bind_agent_session_IP')) !!}
			</div>
			</div>
			</div>
			<div class="row">
			<div class="col-md-4">
                  <div class="form-group">
                  {!! Form::checkbox('reg_require',1,true,['class' => 'form-control']) !!}&nbsp;
				{!! Form::label('reg_require',Lang::get('lang.registration_required')) !!}
			</div>
			</div>
          </div>
			<div class="row">
			<div class="col-md-4">
			<div class="form-group">
			{!! Form::checkbox('quick_access',1,true) !!}&nbsp;
				{!! Form::label('quick_access',Lang::get('lang.client_quick_access')) !!}
			</div>
			</div>
          </div>
		</div>
		</div>
	</div>
	</div>
@stop
</div>
@section('FooterInclude')
@stop
@stop
