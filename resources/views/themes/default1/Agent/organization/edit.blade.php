@extends('themes.default1.layouts.agentblank')
@section('Users')
class="active"
@stop
@section('user-bar')
active
@stop
@section('organizations')
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
{!! Form::model($orgs,['url'=>'organizations/'.$orgs->id,'method'=>'PATCH']) !!}
<div class="box box-primary">
	<div class="content-header">
	 	<h4>{{Lang::get('lang.edit')}}	{!! Form::submit(Lang::get('lang.save'),['class'=>'form-group btn btn-primary pull-right'])!!}</h4>
	</div>
	<div class="box-body">
	<div class="row">
		<div class="col-xs-4 form-group {{ $errors->has('name') ? 'has-error' : '' }}">
			{!! Form::label('name',Lang::get('lang.name')) !!}
			{!! $errors->first('name', '<spam class="help-block">:message</spam>') !!}
			{!! Form::text('name',null,['disabled'=>'disabled','class' => 'form-control']) !!}
		</div>
		<div class="col-xs-4 form-group {{ $errors->has('phone') ? 'has-error' : '' }}">
			{!! Form::label('phone',Lang::get('lang.phone')) !!}
			{!! $errors->first('phone', '<spam class="help-block">:message</spam>') !!}
			{!! Form::text('phone',null,['class' => 'form-control']) !!}
		</div>
		<div class="col-xs-4 form-group {{ $errors->has('website') ? 'has-error' : '' }}">
			{!! Form::label('website',Lang::get('lang.website')) !!}
			{!! $errors->first('website', '<spam class="help-block">:message</spam>') !!}
			{!! Form::text('website',null,['class' => 'form-control']) !!}
		</div>
	</div>
	<div class="row">
		<div class="col-xs-6 form-group">
			{!! Form::label('address',Lang::get('lang.address')) !!}
			{!! Form::textarea('address',null,['class' => 'form-control']) !!}
		</div>
		<div class="col-xs-6 form-group">
			{!! Form::label('internal_notes',Lang::get('lang.internal_notes')) !!}
			{!! Form::textarea('internal_notes',null,['class' => 'form-control']) !!}
		</div>
	</div>
</div>
</div>
@section('FooterInclude')
@stop
@stop
@stop
@section('FooterInclude')
@stop
