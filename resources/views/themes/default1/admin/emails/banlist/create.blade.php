@extends('themes.default1.layouts.blank')
@section('Emails')
class="active"
@stop
@section('emails-bar')
active
@stop
@section('ban')
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
{!! Form::open(['action' => 'Admin\BanlistController@store','method' => 'post']) !!}
	<div class="row">
<div class="col-md-12">
<div class="box box-primary">
<div class="box-header">
	<h3 class="box-title">{{Lang::get('lang.ban_email')}}</h3>
	<div class="pull-right">
	{!! Form::submit(Lang::get('lang.save'),['class'=>'btn btn-primary'])!!}</div>
	</div>
		 <div class="box-body table-responsive"style="overflow:hidden;">
             <div class="row">
               <div class="col-md-6">
		       <div class="form-group {{ $errors->has('ban_status') ? 'has-error' : '' }}">
			{!! Form::label('ban_status',Lang::get('lang.ban_status')) !!}
			<div class="row">
				<div class="col-xs-3">
					{!! Form::radio('ban_status','active',true) !!}{{Lang::get('lang.active')}}
				</div>
				<div class="col-xs-3">
					{!! Form::radio('ban_status','disabled') !!}{{Lang::get('lang.disabled')}}
				</div>
			</div>
		</div>
		<div class="form-group {{ $errors->has('email_address') ? 'has-error' : '' }}">
			{!! Form::label('email_address',Lang::get('lang.email_address')) !!}
			{!! $errors->first('email_address', '<spam class="help-block">:message</spam>') !!}
			{!! Form::text('email_address',null,['class' => 'form-control']) !!}
		</div>
		<div class="form-group">
			{!! Form::label('internal_notes',Lang::get('lang.internal_notes')) !!}
			{!! Form::textarea('internal_notes',null,['class' => 'form-control']) !!}
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
