@extends('themes.default1.layouts.agentblank')
@section('Users')
class="active"
@stop
@section('user-bar')
active
@stop
@section('user')
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
{!! Form::open(['action'=>'Agent\UserController@store','method'=>'post']) !!}
<div class="box box-primary">
	<div class="content-header">
	 	<h4>Create	{!! Form::submit(Lang::get('lang.save'),['class'=>'form-group btn btn-primary pull-right'])!!}</h4>
	</div>
	<div class="box-body">
	<div class="row">
		<div class="col-xs-4 form-group {{ $errors->has('email') ? 'has-error' : '' }}">
			{!! Form::label('email',Lang::get('lang.email')) !!}
			{!! $errors->first('email', '<spam class="help-block">:message</spam>') !!}
			{!! Form::email('email',null,['class' => 'form-control']) !!}
		</div>
		<div class="col-xs-4 form-group {{ $errors->has('full_name') ? 'has-error' : '' }}">
			{!! Form::label('full_name',Lang::get('lang.full_name')) !!}
			{!! $errors->first('full_name', '<spam class="help-block">:message</spam>') !!}
			{!! Form::text('full_name',null,['class' => 'form-control']) !!}
		</div>
		<div class="col-xs-4 form-group {{ $errors->has('phone') ? 'has-error' : '' }}">
			{!! Form::label('phone',Lang::get('lang.phone')) !!}
			{!! $errors->first('phone', '<spam class="help-block">:message</spam>') !!}
			{!! Form::text('phone',null,['class' => 'form-control']) !!}
		</div>
	</div>
		<div class="form-group">
			{!! Form::label('internal_notes',Lang::get('lang.internal_notes')) !!}
			{!! Form::textarea('internal_notes',null,['class' => 'form-control']) !!}
		</div>
</div>
</div>
@section('FooterInclude')
@stop
@stop
@stop
@section('FooterInclude')
@stop
