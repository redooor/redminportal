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
    <form method="POST" action="{{ action('\Redooor\Redminportal\App\Http\Controllers\LoginController@postLogin') }}">
    @csrf
        <div class="card">
            <div class="card-body">
                <h2>{{ Lang::get('redminportal::messages.signin') }}</h2>

                <div class="mb-3">
                <input type="email" name="email" value="{{ old('email', null) }}" class="form-control" placeholder="{{ Lang::get('redminportal::forms.email') }}" required autofocus>
                </div>

                <div class="mb-3">
                <input type="password" name="password" class="form-control" placeholder="{{ Lang::get('redminportal::forms.password') }}" required>
                </div>

                <div class="form-actions text-end">
                <input type="submit" value="{{ Lang::get('redminportal::forms.login') }}" class="btn btn-primary">
                </div>
            </div>
        </div>
    </form>
    </div>
@stop
