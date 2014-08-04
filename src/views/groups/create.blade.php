@extends('redminportal::layouts.master')

@section('content')
	{{ Form::open(array('action' => 'Redooor\Redminportal\GroupController@postStore', 'role' => 'form')) }}
	
		<div class="form-group">
			{{ Form::label('name', 'Name') }}
			{{ Form::text('name', null, array('class' => 'form-control', 'required')) }}
		</div>
		
		<div class="form-group">
			<label for="admin" class="checkbox">
				{{ Form::checkbox('admin', 'yes', true, array('id' => 'admin-checker')) }} Administrator
			</label>
		</div>
		
		<div class="form-group">
			<label for="user" class="checkbox">
				{{ Form::checkbox('user', 'yes', true, array('id' => 'user-checker')) }} User
			</label>
		</div>
		
		<div class='form-actions text-right'>
			{{ HTML::link('admin/groups', 'Cancel', array('class' => 'btn btn-default'))}}
			{{ Form::submit('Create', array('class' => 'btn btn-primary')) }}
		</div>
		
	{{ Form::close() }}
@stop