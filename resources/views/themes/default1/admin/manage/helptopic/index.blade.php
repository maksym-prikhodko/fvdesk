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
	<div class="row">
		<div class="col-xs-12">
			<div class="box box-primary">
               <div class="box-body">
                      <div class="form-group">
                       <div class="box-header">
                            <h2 class="box-title">{{Lang::get('lang.help_topic')}}</h2><a href="{{route('helptopic.create')}}" class="btn btn-primary pull-right">{{Lang::get('lang.create_help_topic')}}</a></div>
				<div class="box-body table-responsive no-padding">
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
				<table class="table table-hover" style="overflow:hidden;">
	<tr>
		<th width="100px">{{Lang::get('lang.topic')}}</th>
		<th width="100px">{{Lang::get('lang.status')}}</th>
		<th width="100px">{{Lang::get('lang.type')}}</th>
		<th width="100px">{{Lang::get('lang.priority')}}</th>
		<th width="100px">{{Lang::get('lang.department')}}</th>
		<th width="100px">{{Lang::get('lang.last_updated')}}</th>
		<th width="100px">{{Lang::get('lang.action')}}</th>
	</tr>
		@foreach($topics as $topic)
	<tr style="padding-bottom:-30px">
		<td><a href="{{route('helptopic.edit',$topic->id)}}">{!! $topic->topic !!}</a></td>
		<td>
			@if($topic->ticket_status=='1')
				<p style="color:green">Active</p>
			@else
				<p style="color:red">Disable</p>
			@endif
		</td>
		<td>
			@if($topic->type=='1')
				<p style="color:green">Public</p>
			@else
				<p style="color:red">Private</p>
			@endif
		</td>
		<td>{!! $topic->priority !!}</td>
		<td>{!! $topic->department !!}</td>
		<td> {!! $topic->updated_at !!} </td>
		<td>
			{!! Form::open(['route'=>['helptopic.destroy', $topic->id],'method'=>'DELETE']) !!}
			<div class="form-group">
				{!! Form::button('<i class="fa fa-star"></i> Delete',
					['type' => 'submit',
					'class'=> 'actions-line icon-trash',
					'onclick'=>'return confirm("Are you sure?")'])
				!!}
			</div>
			{!! Form::close() !!}
		</td>
		@endforeach
	</tr>
</table>
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
