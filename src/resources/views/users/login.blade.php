@extends('redminportal::layouts.plain')

@section('content')
    @if (isset($errors))
        @if(count($errors) > 0)
        <div class="col-lg-6 offset-lg-3">
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
    <div class="col-lg-6 offset-lg-3">
    {!! Form::open(array('action' => '\Redooor\Redminportal\App\Http\Controllers\LoginController@postLogin')) !!}
        <div class="card">
            <div class="card-body">
                <h2>{{ Lang::get('redminportal::messages.signin') }}</h2>

                <div class="mb-3">
                {!! Form::email('email', null, array('class' => 'form-control', 'placeholder' => Lang::get('redminportal::forms.email'), 'required', 'autofocus')) !!}
                </div>

                <div class="mb-3">
                {!! Form::password('password', array('class' => 'form-control', 'placeholder' => Lang::get('redminportal::forms.password'), 'required')) !!}
                </div>

                <div class="form-actions text-end">
                {!! Form::submit(Lang::get('redminportal::forms.login'), array('class' => 'btn btn-primary')) !!}
                </div>
            </div>
        </div>
    {!! Form::close() !!}
    </div>
@stop
