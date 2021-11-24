@extends('themes.default1.layouts.blank')
@section('Settings')
class="active"
@stop
@section('settings-bar')
active
@stop
@section('tickets')
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
	{!! Form::model($tickets,['url' => 'postticket/'.$tickets->id, 'method' => 'PATCH']) !!}
	<div class="row">
		<div class="col-md-12">
			<div class="box box-primary">
				<div class="box-header">
                <h3 class="box-title">{{Lang::get('lang.ticket')}}</h3> <div class="pull-right">
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
               <div class="col-md-6">
               <div class="form-group">
			{!! Form::label('num_format',Lang::get('lang.default_ticket_number_format')) !!}
			{!! $errors->first('num_format', '<spam class="help-block">:message</spam>') !!}
			{!! Form::text('num_format',$tickets->num_format,['class' => 'form-control']) !!}
		</div>
		</div>
            <div class="col-md-6">
		      <div class="form-group {{ $errors->has('num_sequence') ? 'has-error' : '' }}">
			{!! Form::label('num_sequence',Lang::get('lang.default_ticket_number_sequence')) !!}
			{!! $errors->first('num_sequence', '<spam class="help-block">:message</spam>') !!}
			{!!Form::select('num_sequence', ['random','general'],null,['class' => 'form-control select']) !!}
		</div>
		</div>
          <div class="col-md-3">
		<div class="form-group {{ $errors->has('status') ? 'has-error' : '' }}">
			{!! Form::label('status',Lang::get('lang.default_status')) !!}
			{!! $errors->first('status', '<spam class="help-block">:message</spam>') !!}
			{!!Form::select('status', ['open'],null,['class' => 'form-control']) !!}
			</div>
		</div>
          <div class="col-md-3">
		<div class="form-group {{ $errors->has('priority') ? 'has-error' : '' }}">
			{!! Form::label('priority',Lang::get('lang.default_priority')) !!}
			{!! $errors->first('priority', '<spam class="help-block">:message</spam>') !!}
			{!!Form::select('priority', [''=>'select a priority','Priorities'=>$priority->lists('name','name')],null,['class' => 'form-control']) !!}
			</div>
		</div>
       <div class="col-md-3">
		<div class="form-group {{ $errors->has('sla') ? 'has-error' : '' }}">
			{!! Form::label('sla',Lang::get('lang.default_sla')) !!}
			{!! $errors->first('sla', '<spam class="help-block">:message</spam>') !!}
			{!!Form::select('sla', $slas->lists('grace_period','grace_period'),null,['class' => 'form-control']) !!}
			</div>
		</div>
               <div class="col-md-3">
		          <div class="form-group {{ $errors->has('help_topic') ? 'has-error' : '' }}">
			         {!! Form::label('help_topic',Lang::get('lang.default_help_topic')) !!}
			         {!! $errors->first('help_topic', '<spam class="help-block">:message</spam>') !!}
			         {!!Form::select('help_topic', $topics->lists('topic','topic'),null,['class' => 'form-control']) !!}
			    </div>
		    </div>
              <div class="col-md-6">
		        <div class="form-group {{ $errors->has('max_open_ticket') ? 'has-error' : '' }}">
			      {!! Form::label('max_open_ticket',Lang::get('lang.maximum_open_tickets')) !!}
			      {!! $errors->first('max_open_ticket', '<spam class="help-block">:message</spam>') !!}
			      {!! Form::text('max_open_ticket',$tickets->max_open_ticket,['class' => 'form-control']) !!}
			    </div>
		    </div>
                <div class="col-md-6">
		          <div class="form-group {{ $errors->has('collision_avoid') ? 'has-error' : '' }}">
			          {!! Form::label('collision_avoid',Lang::get('lang.agent_collision_avoidance_duration')) !!}
			          {!! $errors->first('collision_avoid', '<spam class="help-block">:message</spam>') !!}
			          {!! Form::text('collision_avoid',$tickets->collision_avoid,['class' => 'form-control']) !!}
			      </div>
		       </div>
            <div class="col-md-6">
		      <div class="form-group">
			    {!! Form::checkbox('captcha',1,true) !!}&nbsp;
		        {!! Form::label('captcha',Lang::get('lang.human_verification')) !!}
			</div>
		<div class="form-group">
			       {!! Form::checkbox('claim_response',1,true) !!}&nbsp;
			{!! Form::label('claim_response',Lang::get('lang.claim_on_response')) !!}
		</div>
		<div class="form-group">
			{!! Form::checkbox('assigned_ticket',1,true) !!}&nbsp;
			{!! Form::label('assigned_ticket',Lang::get('lang.assigned_tickets')) !!}
		</div>
		<div class="form-group">
			{!! Form::checkbox('answered_ticket',1,true) !!}&nbsp;
			{!! Form::label('answered_ticket',Lang::get('lang.answered_tickets')) !!}
		</div>
		<div class="form-group">
			{!! Form::checkbox('agent_mask',1,true) !!}	&nbsp;
			{!! Form::label('agent_mask',Lang::get('lang.agent_identity_masking')) !!}
		</div>
		<div class="form-group">
			{!! Form::checkbox('html',1,true) !!}&nbsp;
			{!! Form::label('html',Lang::get('lang.enable_HTML_ticket_thread')) !!}
		</div>
		<div class="form-group">
			{!! Form::checkbox('client_update',1,true) !!}&nbsp;
			{!! Form::label('client_update',Lang::get('lang.allow_client_updates')) !!}
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
