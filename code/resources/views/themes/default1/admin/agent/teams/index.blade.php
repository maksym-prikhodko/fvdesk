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
	<div class="row">
<div class="col-md-12">
<div class="box box-primary">
<div class="box-header">
	<h2 class="box-title">{{Lang::get('lang.teams')}}</h2><a href="{{route('teams.create')}}" class="btn btn-primary pull-right">{{Lang::get('lang.create_team')}}</a></div>
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
							<th>{{Lang::get('lang.name')}}</th>
							<th>{{Lang::get('lang.status')}}</th>
							<th>{{Lang::get('lang.team_members')}}</th>
							<th>{{Lang::get('lang.team_lead')}}</th>
							<th>{{Lang::get('lang.created')}}</th>
							<th>{{Lang::get('lang.last_updated')}}</th>
							<th>{{Lang::get('lang.action')}}</th>
						</tr>
						@foreach($teams as $team)
						<tr>
							<td><a href="{{route('teams.edit', $team->id)}}"> {{$team -> name }}</a></td>
							<td>
								@if($team->status=='1')
								<p style="color:green">{{'Active'}}</p>
								@else
								<p style="color:red">{{'Inactive'}}</p>
								@endif
							<td>{{count($assign_team_agent->where('team_id',$team->id))}}</td>
							<td>{{$team->team_lead}}</td>
							<td>{{$team -> created_at}}</td>
							<td>{{$team -> updated_at}}</td>
							<td>
							{!! Form::open(['route'=>['teams.destroy', $team->id],'method'=>'DELETE']) !!}
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
</table>
</div>
</div>
</div>
</div>
@section('FooterInclude')
@stop
@stop
