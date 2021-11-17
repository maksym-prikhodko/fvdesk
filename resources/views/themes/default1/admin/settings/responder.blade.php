@extends('themes.default1.layouts.blank')
@section('Settings')
class="active"
@stop
@section('settings-bar')
active
@stop
@section('auto-response')
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
	{!! Form::model($responders,['url' => 'postresponder/'.$responders->id, 'method' => 'PATCH']) !!}
	<div class="row">
		<div class="col-md-12">
			<div class="box box-primary">
				<div class="box-header">
                <h3 class="box-title">{{Lang::get('lang.auto_responce')}}</h3> <div class="pull-right">
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
			<div class="form-group">
				{!! Form::checkbox('new_ticket',1) !!} &nbsp;
				{!! Form::label('new_ticket',Lang::get('lang.new_ticket')) !!}
			</div>
			<div class="form-group">
				{!! Form::checkbox('agent_new_ticket',1) !!}&nbsp;
				{!! Form::label('agent_new_ticket',Lang::get('lang.new_ticket_by_agent')) !!}
			</div>
			<div class="form-group">
				{!! Form::label('new_message',Lang::get('lang.new_message')) !!}<br>
				{!! Form::checkbox('submitter',1,true) !!}&nbsp;{{Lang::get('lang.submitter')}}&nbsp;{{Lang::get('lang.send_receipt_confirmation')}}&nbsp;&nbsp;&nbsp;
				{!! Form::checkbox('partcipants',1) !!}&nbsp;{{Lang::get('lang.participants')}}&nbsp;{{Lang::get('lang.send_new_activity_notice')}}
			</div>
			<div class="form-group">
				{!! Form::checkbox('overlimit',1) !!}&nbsp;
				{!! Form::label('overlimit',Lang::get('lang.overlimit_notice')) !!}
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
