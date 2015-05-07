@extends('redminportal::layouts.master')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <ol class="breadcrumb">
                <li><a href="{{ URL::to('admin') }}">{{ Lang::get('redminportal::menus.home') }}</a></li>
                <li><a href="{{ URL::to('admin/users') }}">{{ Lang::get('redminportal::menus.users') }}</a></li>
                <li class="active">{{ Lang::get('redminportal::forms.create') }}</li>
            </ol>
        </div>
    </div>
    
    @include('redminportal::partials.errors')

	{!! Form::open(array('action' => '\Redooor\Redminportal\App\Http\Controllers\UserController@postStore', 'role' => 'form')) !!}
    <div class="row">
        <div class="col-md-9">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4 class="panel-title">{{ Lang::get('redminportal::forms.create_user') }}</h4>
                </div>
                <div class="panel-body">
                    <div class="form-group">
                        {!! Form::label('first_name', 'First Name') !!}
                        {!! Form::text('first_name', null, array('class' => 'form-control', 'autofocus', 'required')) !!}
                    </div>

                    <div class="form-group">
                        {!! Form::label('last_name', 'Last Name') !!}
                        {!! Form::text('last_name', null, array('class' => 'form-control', 'required')) !!}
                    </div>

                    <div class="form-group">
                        {!! Form::label('email', 'Email') !!}
                        {!! Form::email('email', null, array('class' => 'form-control', 'required')) !!}
                    </div>

                    <div class="form-group">
                        {!! Form::label('password', 'Password') !!}
                        {!! Form::password('password', array('class' => 'form-control', 'required')) !!}
                    </div>

                    <div class="form-group">
                        {!! Form::label('password_confirmation', 'Re-enter Password') !!}
                        {!! Form::password('password_confirmation', array('class' => 'form-control', 'required')) !!}
                    </div>

                    <div class="form-group">
                        {!! Form::label('role', 'Role') !!}
                        {!! Form::select('role', $roles, null, array('class' => 'form-control')) !!}
                    </div>
                    <div class="well">
                        <div class="form-group">
                            <div class="checkbox">
                                <label for="activated-checker">
                                    {!! Form::checkbox('activated', 'yes', true, array('id' => 'activated-checker')) !!} Activate Now
                                </label>
                            </div>
                            <p class="help-block">Allow user to log in to this account</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="well">
                <div class='form-actions'>
                    {!! HTML::link('admin/users', Lang::get('redminportal::buttons.cancel'), array('class' => 'btn btn-link btn-sm'))!!}
                    {!! Form::submit(Lang::get('redminportal::buttons.create'), array('class' => 'btn btn-primary btn-sm pull-right')) !!}
                </div>
            </div>
        </div>
    </div>
	{!! Form::close() !!}
@stop
