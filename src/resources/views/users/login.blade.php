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
    <div class="col-md-4 col-md-offset-4 form-signin">
    {{ Form::open(array('action' => 'Redooor\Redminportal\LoginController@postLogin')) }}
        <h2 class="form-signin-heading">{{ Lang::get('redminportal::messages.signin') }}</h2>

        <div class="form-group">
        {{ Form::email('email', null, array('class' => 'form-control', 'placeholder' => Lang::get('redminportal::forms.email'), 'required', 'autofocus')) }}
        </div>

        <div class="form-group">
        {{ Form::password('password', array('class' => 'form-control', 'placeholder' => Lang::get('redminportal::forms.password'), 'required')) }}
        </div>

        <div class="form-actions text-right">
        {{ Form::submit(Lang::get('redminportal::forms.login'), array('class' => 'btn btn-primary')) }}
        </div>
    {{ Form::close() }}
    </div>
@stop
