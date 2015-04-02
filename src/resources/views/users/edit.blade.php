@extends('redminportal::layouts.master')

@section('content')
    @if (isset($errors))
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
    @endif
    {!! Form::open(array('action' => '\Redooor\Redminportal\App\Http\Controllers\UserController@postStore', 'role' => 'form')) !!}
    {!! Form::hidden('id', $user->id) !!}
		<div class="form-group">
			{!! Form::label('first_name', 'First Name') !!}
			{!! Form::text('first_name', $user->first_name, array('class' => 'form-control', 'required')) !!}
		</div>

		<div class="form-group">
			{!! Form::label('last_name', 'Last Name') !!}
			{!! Form::text('last_name', $user->last_name, array('class' => 'form-control', 'required')) !!}
		</div>

		<div class="form-group">
			{!! Form::label('email', 'Email') !!}
			{!! Form::email('email', $user->email, array('class' => 'form-control', 'required')) !!}
		</div>

		<div class="form-group">
			{!! Form::label('password', 'Password') !!}
			{!! Form::password('password', array('class' => 'form-control')) !!}
            <p class="help-block">Leave the password field empty to keep existing password.</p>
		</div>

		<div class="form-group">
            {!! Form::label('password_confirmation', 'Re-enter Password') !!}
            {!! Form::password('password_confirmation', array('class' => 'form-control')) !!}
        </div>

		<div class="form-group">
			{!! Form::label('role', 'Role') !!}
			{!! Form::select('role', $roles, $group->id, array('class' => 'form-control')) !!}
		</div>

		<div class="form-group">
            <div class="checkbox">
                <label for="activated-checker">
                    {!! Form::checkbox('activated', 'yes', $user->activated, array('id' => 'activated-checker')) !!} Activate Now
                </label>
            </div>
			<p class="help-block">Allow user to log in to this account</p>
		</div>

		<div class='form-actions text-right'>
			{!! HTML::link('admin/users', 'Cancel', array('class' => 'btn btn-default'))!!}
			{!! Form::submit('Save Changes', array('class' => 'btn btn-primary')) !!}
		</div>

	{!! Form::close() !!}
@stop
