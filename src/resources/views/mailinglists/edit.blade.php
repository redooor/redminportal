@extends('redminportal::layouts.master')

@section('navbar-breadcrumb')
    <li class="nav-item"><a class="nav-link" href="{{ URL::to('admin/mailinglists') }}">{{ Lang::get('redminportal::menus.mailinglist') }}</a></li>
    <li class="nav-item"><span class="nav-link active">{{ Lang::get('redminportal::forms.edit') }}</span></li>
@stop

@section('content')
    
    @include('redminportal::partials.errors')

    <form method="POST" action="{{ action('\Redooor\Redminportal\App\Http\Controllers\MailinglistController@postStore') }}" enctype="multipart/form-data" role="form">
        @csrf
        <input type="hidden" name="id" value="{{ old('id', $mailinglist->id) }}">

        <div class="row">
            <div class="col-lg-9">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0">{{ Lang::get('redminportal::forms.edit_mailinglist') }}</h4>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="first_name">{{ Lang::get('redminportal::forms.first_name') }}</label>
                            <input type="text" name="first_name" id="first_name" value="{{ old('first_name', $mailinglist->first_name) }}" class="form-control" required autofocus>
                        </div>

                        <div class="mb-3">
                            <label for="last_name">{{ Lang::get('redminportal::forms.last_name') }}</label>
                            <input type="text" name="last_name" id="last_name" value="{{ old('last_name', $mailinglist->last_name) }}" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label for="email">{{ Lang::get('redminportal::forms.email') }}</label>
                            <input type="email" name="email" id="email" value="{{ old('email', $mailinglist->email) }}" class="form-control" required>
                        </div>
                        
                        <div class="card card-body bg-body-tertiary">
                            <div class="mb-3">
                                <div class="form-check">
                                    <label class="form-check-label" for="active-checker">
                                        <input type="checkbox" name="active" value="1" id="active-checker" class="form-check-input" @checked(session()->hasOldInput() ? old('active') : $mailinglist->active)> {{ Lang::get('redminportal::forms.active') }}
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3">
                <div class='card card-body bg-body-tertiary'>
                    <div class='form-actions'>
                        <a href="{{ URL::to('admin/mailinglists') }}" class="btn btn-link btn-sm">{{ Lang::get('redminportal::buttons.cancel') }}</a>
                        <input type="submit" value="{{ Lang::get('redminportal::buttons.save') }}" class="btn btn-primary btn-sm float-end">
                    </div>
                </div>
            </div>
        </div>
    </form>
@stop

@section('footer')
@stop
