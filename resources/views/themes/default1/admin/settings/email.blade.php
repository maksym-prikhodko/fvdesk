@extends('themes.default1.layouts.blank')
@section('Settings')
class="active"
@stop
@section('settings-bar')
active
@stop
@section('email')
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
	{!! Form::model($emails,['url' => 'postemail/'.$emails->id, 'method' => 'PATCH']) !!}
          <div class="row">
            <div class="col-md-12">
              <div class="box box-primary">
                <div class="box-header">
                <h3 class="box-title">{{Lang::get('lang.email')}}</h3> <div class="pull-right">
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
               <div class="col-md-3">
                  <div class="form-group {{ $errors->has('template') ? 'has-error' : '' }}">
                     {!! Form::label('template',Lang::get('lang.default_template')) !!}
				     {!! $errors->first('template', '<spam class="help-block">:message</spam>') !!}
				     {!!Form::select('template', $templates->lists('name','name'),null,['class'=>'form-control']) !!}
				</div>
			</div>
                 <div class="col-md-3">
			       <div class="form-group {{ $errors->has('sys_email') ? 'has-error' : '' }}">
                     {!! Form::label('sys_email',Lang::get('lang.default_system_email')) !!}
				      {!! $errors->first('sys_email', '<spam class="help-block">:message</spam>') !!}
				      {!!Form::select('sys_email', $emails1->lists('email_address','email_address'),null,['class'=>'form-control']) !!}
				</div>
			</div>
                    <div class="col-md-3">
			           <div class="form-group {{ $errors->has('alert_email') ? 'has-error' : '' }}">
                       {!! Form::label('alert_email',Lang::get('lang.default_alert_email')) !!}
				       {!! $errors->first('alert_email', '<spam class="help-block">:message</spam>') !!}
				       {!!Form::select('alert_email',  $emails1->lists('email_address','email_address'),null,['class'=>'form-control']) !!}
				    </div>
			     </div>
			         <div class="col-md-3">
			           <div class="form-group">
                       {!! Form::label('alert_email',Lang::get('lang.default_MTA')) !!}
				       {!!Form::select('alert_email',['use PHP Mail function'],null,['class'=>'form-control']) !!}
				      </div>
			       </div>
			       <div class="col-md-6">
			       <div class="form-group">
			           {!! Form::label('email_fetching',Lang::get('lang.email_fetch')) !!}<br>
			           {!! Form::checkbox('email_fetching',1,true) !!}&nbsp;{{Lang::get('lang.fetch_auto-corn')}}
			          </div>
		           </div>
		           </div>
		            <div class="row">
		              <div class="col-md-6">
                        <div class="form-group">
                         {!! Form::checkbox('all_emails',1,true) !!}&nbsp;{{Lang::get('lang.accept_all_email')}}
				         </div>
			          </div>
                     </div>
		           <div class="row">
                      <div class="col-md-6">
			             <div class="form-group {{ $errors->has('admin_email') ? 'has-error' : '' }}">
				           {!! Form::label('admin_email',Lang::get('lang.admin_email')) !!}
				           {!! $errors->first('admin_email', '<spam class="help-block">:message</spam>') !!}
				            {!! Form::text('admin_email',null,['class' => 'form-control']) !!}
				          </div>
			          </div>
			           <div class="col-md-6">
			               <div class="form-group {{ $errors->has('separator') ? 'has-error' : '' }}">
			                 {!! Form::label('separator',Lang::get('lang.reply_separator')) !!}
				             {!! $errors->first('separator', '<spam class="help-block">:message</spam>') !!}
				              {!! Form::text('separator',null,['class' => 'form-control']) !!}
			           	</div>
			          </div>
			          </div>
                          <div class="row">
                           <div class="col-md-4">
			                <div class="form-group">
			                 {!! Form::checkbox('email_collaborator',1) !!}&nbsp;{{Lang::get('lang.accept_email_collab')}}
				              </div>
			                 </div>
			                 </div>
                         <div class="row">
                         <div class="col-md-4">
		                   <div class="form-group">
		                   {!! Form::checkbox('strip',1,['class' => 'form-control']) !!}&nbsp;{{Lang::get('lang.strip_quoted_reply')}}
		                  </div>
                          </div>
                          </div>
                          <div class="row">
                             <div class="col-md-4">
			                 <div class="form-group">
				              {!! Form::checkbox('attachment',1) !!}&nbsp;{{Lang::get('lang.attachments')}}
				          </div>
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
