@extends('redminportal::layouts.master')

@section('content')
    @if($errors->has())
    <div class='alert alert-danger'>
        {{ Lang::get('messages.errors') }}
        <ul>
            @foreach($errors->all() as $message)
            <li>{{ $message }}</li>
            @endforeach
        </ul>
    </div>
    @endif
    <div class="col-md-4 col-md-offset-4 form-signin">
    {{ Form::open(array('action' => 'Redooor\Redminportal\LoginController@postLogin')) }}
        <h2 class="form-signin-heading">Please sign in</h2>

        <div class="form-group">
        {{ Form::email('email', null, array('class' => 'form-control', 'placeholder' => 'Email address', 'required')) }}
        </div>

        <div class="form-group">
        {{ Form::password('password', array('class' => 'form-control', 'placeholder' => 'Password', 'required')) }}
        </div>

        <div class="form-actions text-right">
        {{ Form::submit('Login', array('class' => 'btn btn-primary')) }}
        </div>
    {{ Form::close() }}
    </div>
@stop
