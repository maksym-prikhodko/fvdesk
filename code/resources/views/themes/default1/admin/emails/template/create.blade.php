@extends('themes.default1.layouts.blank')
@section('Emails')
class="active"
@stop
@section('emails-bar')
active
@stop
@section('template')
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
{!! Form::open(['action' => 'Admin\TemplateController@store','method' => 'post']) !!}
	<div class="row">
<div class="col-md-12">
<div class="box box-primary">
<div class="box-body">
<div class="box-header">
<h2 class="box-title">{{Lang::get('lang.create')}}</h2>
<div class="pull-right">
   {!! Form::submit(Lang::get('lang.save'),['class'=>'btn btn-primary'])!!}</div>
   </div>
	 <div class="box-body table-responsive no-padding"style="overflow:hidden">
	    <div class="row">
		<div class="col-md-6 form-group {{ $errors->has('ban_status') ? 'has-error' : ''}}">
			<div class="row col-xs-3">
			{!! Form::label('status',Lang::get('lang.status')) !!}
			</div>
			<div class="row">
				<div class="col-xs-3">
					{!! Form::radio('ban_status','active',true) !!}{{Lang::get('lang.active')}}
				</div>
				<div class="col-xs-3">
					{!! Form::radio('ban_status','disabled') !!}{{Lang::get('lang.disabled')}}
				</div>
			</div>
			</div>
		</div>
		<div class="row">
           <div class="col-md-4">
		        <div class="form-group {{ $errors->has('name') ? 'has-error' : '' }}">
			      {!! Form::label('name',Lang::get('lang.name')) !!}
			      {!! $errors->first('name', '<spam class="help-block">:message</spam>') !!}
			       {!! Form::text('name',null,['class' => 'form-control']) !!}
			</div>
		</div>
             <div class="col-md-4">
		<div class="form-group {{ $errors->has('template_set_to_clone') ? 'has-error' : '' }}">
			{!! Form::label('template_set_to_clone',Lang::get('lang.template_set_to_clone')) !!}
			{!! $errors->first('template_set_to_clone', '<spam class="help-block">:message</spam>') !!}
			{!!Form::select('template_set_to_clone', [''=>'Select a Template','Templates'=>$templates->lists('name','name')],1,['class' => 'form-control']) !!}
			</div>
		</div>
           <div class="col-md-4">
		<div class="form-group {{ $errors->has('language') ? 'has-error' : '' }}">
			{!! Form::label('language',Lang::get('lang.language')) !!}
			{!! $errors->first('language', '<spam class="help-block">:message</spam>') !!}
			{!!Form::select('language', [''=>'Select a Language','Languages'=>$languages->lists('name','name')],null,['class' => 'form-control']) !!}
			</div>
		</div>
             <div class="col-md-12">
		      <div class="form-group">
			     {!! Form::label('internal_note',Lang::get('lang.internal_notes')) !!}
			     {!! Form::textarea('internal_note',null,['class' => 'form-control']) !!}
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