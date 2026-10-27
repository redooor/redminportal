@extends('redminportal::layouts.master')

@section('navbar-breadcrumb')
    <li class="nav-item"><a class="nav-link" href="{{ URL::to('admin/mailinglists') }}">{{ Lang::get('redminportal::menus.mailinglist') }}</a></li>
    <li class="nav-item"><span class="nav-link active">{{ Lang::get('redminportal::forms.create') }}</span></li>
@stop

@section('content')
    
    @include('redminportal::partials.errors')

    {!! Form::open(array('files' => TRUE, 'action' => '\Redooor\Redminportal\App\Http\Controllers\MailinglistController@postStore', 'role' => 'form')) !!}

        <div class="row">
            <div class="col-lg-9">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0">{{ Lang::get('redminportal::forms.create_mailinglist') }}</h4>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            {!! Form::label('first_name', Lang::get('redminportal::forms.first_name')) !!}
                            {!! Form::text('first_name', null, array('class' => 'form-control', 'required', 'autofocus')) !!}
                        </div>

                        <div class="mb-3">
                            {!! Form::label('last_name', Lang::get('redminportal::forms.last_name')) !!}
                            {!! Form::text('last_name', null, array('class' => 'form-control', 'required')) !!}
                        </div>

                        <div class="mb-3">
                            {!! Form::label('email', Lang::get('redminportal::forms.email')) !!}
                            {!! Form::email('email', null, array('class' => 'form-control', 'required')) !!}
                        </div>
                        
                        <div class="card card-body bg-body-tertiary">
                            <div class="mb-3">
                                <div class="form-check">
                                    <label class="form-check-label" for="active-checker">
                                        <input class="form-check-input" id="active-checker" checked="checked" name="active" type="checkbox" value="1"> {{ Lang::get('redminportal::forms.active') }}
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
                        {!! HTML::link('admin/mailinglists', Lang::get('redminportal::buttons.cancel'), array('class' => 'btn btn-link btn-sm'))!!}
                        {!! Form::submit(Lang::get('redminportal::buttons.create'), array('class' => 'btn btn-primary btn-sm float-end')) !!}
                    </div>
                </div>
            </div>
        </div>
    {!! Form::close() !!}
@stop

@section('footer')
@stop
