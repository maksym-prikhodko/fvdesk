@extends('themes.default1.layouts.blank')
@section('Staffs')
class="active"
@stop
@section('staffs-bar')
active
@stop
@section('groups')
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
<div class="col-md-12">
<div class="box box-primary">
<div class="box-header">
	<h2 class="box-title">{{Lang::get('lang.group')}}</h2><a href="{{route('groups.create')}}" class="btn btn-primary pull-right">{{Lang::get('lang.create_group')}}</a></div>
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
							<th>{{Lang::get('lang.group_name')}}</th>
							<th>{{Lang::get('lang.status')}}</th>
							<th>{{Lang::get('lang.group_members')}}</th>
							<th>{{Lang::get('lang.created')}}</th>
							<th>{{Lang::get('lang.last_updated')}}</th>
							<th>{{Lang::get('lang.action')}}</th>
						</tr>
						@foreach($groups as $group)
						<tr>
							<td><a href="{{route('groups.edit', $group->id)}}"> {{$group -> name }}</a></td>
							<td>
								@if($group->group_status=='1')
								<p style="color:green">{{'Active'}}</p>
								@else
								<p style="color:red">{{'Inactive'}}</p>
								@endif
							<td>{{count($group_assign_department->where('group_id',$group->id))}}</td>
							<td>{{$group -> created_at}}</td>
							<td>{{$group -> updated_at}}</td>
							<td>
							{!! Form::open(['route'=>['groups.destroy', $group->id],'method'=>'DELETE']) !!}
							 <div class="form-group">
								{!! Form::button('<i class="fa fa-star"></i> Delete',
				            		['type' => 'submit',
				            		'class'=> 'actions-line icon-trash',
				            		'onclick'=>'return confirm("Are you sure?")'])
				            	!!}
							</div>
							{!! Form::close() !!}
							</td>
						</tr>
						@endforeach
</td>
</tr>
</tr>
</table>
</div>
</div>
</div>
</div>
@section('FooterInclude')
@stop
@stop
