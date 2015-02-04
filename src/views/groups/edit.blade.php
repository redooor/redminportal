@extends('redminportal::layouts.master')

@section('content')
    @if($errors->has())
    <div class='alert alert-danger'>
        We encountered the following errors:
        <ul>
            @foreach($errors->all() as $message)
            <li>{{ $message }}</li>
            @endforeach
        </ul>
    </div>
    @endif

	{{ Form::open(array('action' => 'Redooor\Redminportal\GroupController@postStore', 'role' => 'form')) }}
    {{ Form::hidden('id', $group->id) }}
		<div class="form-group">
			{{ Form::label('name', 'Name') }}
			{{ Form::text('name', $group->name, array('class' => 'form-control', 'required')) }}
		</div>
		
		<div class="form-group">
            <div class="checkbox">
                <label for="admin-checker">
                    {{ Form::checkbox('admin', 'yes', $checkbox_admin, array('id' => 'admin-checker')) }} Administrator
                </label>
            </div>
		</div>
		
		<div class="form-group">
            <div class="checkbox">
                <label for="user-checker">
                    {{ Form::checkbox('user', 'yes', $checkbox_users, array('id' => 'user-checker')) }} User
                </label>
            </div>
		</div>
		
		<div class='form-actions text-right'>
			{{ HTML::link('admin/groups', 'Cancel', array('class' => 'btn btn-default'))}}
			{{ Form::submit('Save Changes', array('class' => 'btn btn-primary')) }}
		</div>
		
	{{ Form::close() }}
@stop