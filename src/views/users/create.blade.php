@extends('redminportal::layouts.master')

@section('content')
    @if($errors->has())
    <div class='alert alert-danger'>
        {{ Lang::get('redminportal::messages.error') }}
        <ul>
            @foreach($errors->all() as $message)
            <li>{{ $message }}</li>
            @endforeach
        </ul>
    </div>
    @endif
	{{ Form::open(array('action' => 'Redooor\Redminportal\UserController@postStore', 'role' => 'form')) }}

		<div class="form-group">
			{{ Form::label('first_name', 'First Name') }}
			{{ Form::text('first_name', null, array('class' => 'form-control', 'required')) }}
		</div>

		<div class="form-group">
			{{ Form::label('last_name', 'Last Name') }}
			{{ Form::text('last_name', null, array('class' => 'form-control', 'required')) }}
		</div>

		<div class="form-group">
			{{ Form::label('email', 'Email') }}
			{{ Form::email('email', null, array('class' => 'form-control', 'required')) }}
		</div>

		<div class="form-group">
			{{ Form::label('password', 'Password') }}
			{{ Form::password('password', array('class' => 'form-control', 'required')) }}
		</div>

		<div class="form-group">
            {{ Form::label('password_confirmation', 'Re-enter Password') }}
            {{ Form::password('password_confirmation', array('class' => 'form-control', 'required')) }}
        </div>

		<div class="form-group">
			{{ Form::label('role', 'Role') }}
			{{ Form::select('role', $roles, null, array('class' => 'form-control')) }}
		</div>

		<div class="form-group">
			<label for="activated" class="checkbox">
				{{ Form::checkbox('activated', 'yes', true, array('id' => 'activated-checker')) }} Activate Now
			</label>
			<p class="help-block">Allow user to log in to this account</p>
		</div>

		<div class='form-actions text-right'>
			{{ HTML::link('admin/users', 'Cancel', array('class' => 'btn btn-default'))}}
			{{ Form::submit('Create', array('class' => 'btn btn-primary')) }}
		</div>

	{{ Form::close() }}
@stop
