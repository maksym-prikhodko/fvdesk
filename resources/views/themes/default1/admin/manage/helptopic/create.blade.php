@extends('themes.default1.layouts.blank')
@section('Manage')
class="active"
@stop
@section('manage-bar')
active
@stop
@section('help')
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
	{!! Form::open(['action' => 'Admin\HelptopicController@store', 'method' => 'post']) !!}
	<div class="row">
		<div class="col-md-12">
			<div class="box box-primary">
               <div class="box-body">
                      <div class="form-group">
                       <div class="box-header">
                            <h2 class="box-title">{{Lang::get('lang.create')}}</h2><div class="pull-right">
                            {!! Form::submit(Lang::get('lang.save'),['class'=>'btn btn-primary'])!!}
                            </div>
                            </div>
	 <div class="box-body table-responsive no-padding" style="overflow:hidden">
                          <div class="row">
               <div class="col-md-6">
		<div class="form-group {{ $errors->has('ticket_status') ? 'has-error' : '' }}">
			{!! Form::label('ticket_status',Lang::get('lang.status')) !!}&nbsp;&nbsp;
			{!! $errors->first('ticket_status', '<spam class="help-block">:message</spam>') !!}
			{!! Form::radio('ticket_status','1',true) !!}{{Lang::get('lang.active')}}&nbsp;&nbsp;&nbsp;
			{!! Form::radio('ticket_status','0') !!}{{Lang::get('lang.disabled')}}
			</div>
		</div>
            <div class="col-md-6">
		<div class="form-group {{ $errors->has('type') ? 'has-error' : '' }}">
			{!! Form::label('type',Lang::get('lang.type')) !!}&nbsp;&nbsp;
			{!! $errors->first('type', '<spam class="help-block">:message</spam>') !!}
			{!! Form::radio('type','1',true) !!}{{Lang::get('lang.public')}}&nbsp;&nbsp;&nbsp;
			{!! Form::radio('type','0') !!}{{Lang::get('lang.private')}}
		</div>
		</div>
          <div class="col-md-6">
		<div class="form-group {{ $errors->has('topic') ? 'has-error' : '' }}">
			{!! Form::label('topic',Lang::get('lang.topic')) !!}
			{!! $errors->first('topic', '<spam class="help-block">:message</spam>') !!}
			{!! Form::text('topic',null,['class' => 'form-control']) !!}
			</div>
		</div>
              <div class="col-md-6">
		<div class="form-group {{ $errors->has('parent_topic') ? 'has-error' : '' }}">
			{!! Form::label('parent_topic',Lang::get('lang.parent_topic')) !!}
			{!! $errors->first('parent_topic', '<spam class="help-block">:message</spam>') !!}
			{!!Form::select('parent_topic', [''=>'Select a Parent Topic','Help Topics'=>$topics->lists('topic','topic')],1,['class' => 'form-control']) !!}
			</div>
		</div>
           <div class="col-md-6">
		<div class="form-group {{ $errors->has('custom_form') ? 'has-error' : '' }}">
			{!! Form::label('custom_form',Lang::get('lang.custom')) !!}
			{!! $errors->first('custom_form', '<spam class="help-block">:message</spam>') !!}
			{!!Form::select('custom_form', [''=>'Select a Form','Custom Forms'=>$forms->lists('name','name')],1,['class' => 'form-control']) !!}
			</div>
		</div>
            <div class="col-md-6">
		<div class="form-group {{ $errors->has('department') ? 'has-error' : '' }}">
			{!! Form::label('department',Lang::get('lang.department')) !!}
			{!! $errors->first('department', '<spam class="help-block">:message</spam>') !!}
			{!!Form::select('department', [''=>'Select a Department','Departments'=>$departments->lists('name','name')],1,['class' => 'form-control']) !!}
			</div>
		</div>
</div>
          <div class="row">
             <div class="col-md-4">
		<div class="form-group {{ $errors->has('priority') ? 'has-error' : '' }}">
			{!! Form::label('priority',Lang::get('lang.priority')) !!}
			{!! $errors->first('priority', '<spam class="help-block">:message</spam>') !!}
			{!!Form::select('priority', [''=>'Select a Proirity','Priorities'=>$priority->lists('name','name')],null,['class' => 'form-control']) !!}
			</div>
		</div>
             <div class="col-md-4">
		<div class="form-group {{ $errors->has('sla_plan') ? 'has-error' : '' }}">
			{!! Form::label('sla_plan',Lang::get('lang.SLA_plan')) !!}
			{!! $errors->first('sla_plan', '<spam class="help-block">:message</spam>') !!}
			{!!Form::select('sla_plan', [''=>'Select a SLA Plan','SLA Plans'=>$slas->lists('name','name')],1,['class' => 'form-control']) !!}
			</div>
		</div>
          <div class="col-md-4">
		<div class="form-group {{ $errors->has('auto_assign') ? 'has-error' : '' }}">
			{!! Form::label('auto_assign',Lang::get('lang.auto_assign')) !!}
			{!! $errors->first('auto_assign', '<spam class="help-block">:message</spam>') !!}
			{!!Form::select('auto_assign', [''=>'Select a Agent','Agents'=>$agents->lists('user_name','user_name')],null,['class' => 'form-control']) !!}
			</div>
		</div>
		</div>
          <div class="row">
          <div class="col-md-6">
		<div class="form-group {{ $errors->has('auto_response') ? 'has-error' : '' }}">
			{!! Form::label('auto_response',Lang::get('lang.auto_response')) !!}&nbsp;
			{!! $errors->first('auto_response', '<spam class="help-block">:message</spam>') !!}
			{!! Form::checkbox('auto_response',1,true) !!}
		</div>
        </div>
         <div class="col-md-6">
		<div class="form-group {{ $errors->has('ticket_num_format') ? 'has-error' : '' }}">
			{!! Form::label('ticket_num_format',Lang::get('lang.ticket_number_format')) !!}&nbsp;
			{!! $errors->first('ticket_num_format', '<spam class="help-block">:message</spam>') !!}
			{!! Form::radio('ticket_num_format','1',true) !!}System Default&nbsp;&nbsp;&nbsp;
			{!! Form::radio('ticket_num_format','0') !!}Custom
			</div>
		</div>
<div class="col-md-12">
		<div class="form-group">
			{!! Form::label('internal_notes',Lang::get('lang.internal_notes')) !!}
			{!! Form::textarea('internal_notes',null,['class' => 'form-control','size' => '10x5']) !!}
		</div>
		</div>
		</div>
</div>
</div>
</div>
</div>
</div>
</div>
{!! Form::close() !!}
@stop
</div>
@section('FooterInclude')
@stop
@stop
