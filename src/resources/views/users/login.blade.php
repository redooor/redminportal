@extends('redminportal::layouts.plain')

@section('content')
    @if (isset($errors))
        @if($errors->has())
        <div class="col-md-6 col-md-offset-3">
            <div class='alert alert-danger'>
                {{ Lang::get('redminportal::messages.error') }}
                <ul>
                    @foreach($errors->all() as $message)
                    <li>{{ $message }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
        @endif
    @endif
    <div class="col-md-6 col-md-offset-3">
    {!! Form::open(array('action' => '\Redooor\Redminportal\App\Http\Controllers\LoginController@postLogin')) !!}
        <input type="hidden" name="_token" id="csrf-token" value="{{ Session::token() }}" />
        <div class="panel panel-default">
            <div class="panel-body">
                <h2>{{ Lang::get('redminportal::messages.signin') }}</h2>

                <div class="form-group">
                {!! Form::email('email', null, array('class' => 'form-control', 'placeholder' => Lang::get('redminportal::forms.email'), 'required', 'autofocus')) !!}
                </div>

                <div class="form-group">
                {!! Form::password('password', array('class' => 'form-control', 'placeholder' => Lang::get('redminportal::forms.password'), 'required')) !!}
                </div>

                <div class="form-actions text-right">
                {!! Form::submit(Lang::get('redminportal::forms.login'), array('class' => 'btn btn-primary')) !!}
                </div>
            </div>
        </div>
    {!! Form::close() !!}
    </div>
@stop
