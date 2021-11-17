@extends('themes.default1.layouts.blank')
@section('Staffs')
class="active"
@stop
@section('staffs-bar')
active
@stop
@section('teams')
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
{!! Form::open(array('action' => 'Admin\TeamController@store' , 'method' => 'post') )!!}
	<div class="box box-primary">
	<div class="content-header">
	 	<h4>Create	{!! Form::submit(Lang::get('lang.save'),['class'=>'form-group btn btn-primary pull-right'])!!}</h4>
	</div>
	<div class="box-body">
	<div class="row">
		<div class="col-xs-6 form-group {{ $errors->has('name') ? 'has-error' : '' }}">
			{!! Form::label('name',Lang::get('lang.name')) !!}
			{!! $errors->first('name', '<spam class="help-block">:message</spam>') !!}
			{!! Form::text('name',null,['class' => 'form-control']) !!}
		</div>
		<div class="col-xs-6 form-group {{ $errors->has('team_lead') ? 'has-error' : '' }}">
			{!! Form::label('team_lead',Lang::get('lang.team_lead')) !!}
			{!! $errors->first('team_lead', '<spam class="help-block">:message</spam>') !!}
			{!!Form::select('team_lead',[''=>'Select a Team Lead','Team Members'=>$user->where('role','agent')->lists('user_name','user_name')],null,['class' => 'form-control select']) !!}
		</div>
	</div>
		<div class="form-group {{ $errors->has('status') ? 'has-error' : '' }}">
			{!! Form::label('status',Lang::get('lang.status')) !!}
			{!! $errors->first('status', '<spam class="help-block">:message</spam>') !!}
			<div class="row">
				<div class="col-xs-1">
					{!! Form::radio('status','1',true) !!}{{Lang::get('lang.active')}}
				</div>
				<div class="col-xs-2">
					{!! Form::radio('status','0',null) !!}{{Lang::get('lang.disabled')}}
				</div>
			</div>
		</div>
		<div class="row  form-group">
			<div class="col-xs-2">
			{!! Form::label('assign_alert',Lang::get('lang.assignment_alert')) !!}
			</div>
			<div class="col-xs-2">
				{!! Form::checkbox('assign_alert',1,null,null,['class' => 'form-control']) !!}
				{{Lang::get('lang.disable_for_this_team')}}
			</div>
		</div>
		<div class="form-group">
			{!! Form::label('admin_notes',Lang::get('lang.admin_notes')) !!}
			{!! Form::textarea('admin_notes',null,['class' => 'form-control','size' => '30x5']) !!}
		</div>
{!!Form::close()!!}
</div>
</div>
@section('FooterInclude')
@stop
@stop
