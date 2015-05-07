@extends('redminportal::layouts.master')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <ol class="breadcrumb">
                <li><a href="{{ URL::to('admin') }}">{{ Lang::get('redminportal::menus.home') }}</a></li>
                <li><a href="{{ URL::to('admin/mailinglists') }}">{{ Lang::get('redminportal::menus.mailinglist') }}</a></li>
                <li class="active">{{ Lang::get('redminportal::forms.create') }}</li>
            </ol>
        </div>
    </div>
    
    @include('redminportal::partials.errors')

    {!! Form::open(array('files' => TRUE, 'action' => '\Redooor\Redminportal\App\Http\Controllers\MailinglistController@postStore', 'role' => 'form')) !!}

        <div class="row">
            <div class="col-md-9">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h4 class="panel-title">{{ Lang::get('redminportal::forms.create_mailinglist') }}</h4>
                    </div>
                    <div class="panel-body">
                        <div class="form-group">
                            {!! Form::label('first_name', 'First Name') !!}
                            {!! Form::text('first_name', null, array('class' => 'form-control', 'required', 'autofocus')) !!}
                        </div>

                        <div class="form-group">
                            {!! Form::label('last_name', 'Last Name') !!}
                            {!! Form::text('last_name', null, array('class' => 'form-control', 'required')) !!}
                        </div>

                        <div class="form-group">
                            {!! Form::label('email', 'Email') !!}
                            {!! Form::email('email', null, array('class' => 'form-control', 'required')) !!}
                        </div>
                        
                        <div class="well">
                            <div class="form-group">
                                <div class="checkbox">
                                    <label for="active-checker">
                                        {!! Form::checkbox('active', true, true, array('id' => 'active-checker')) !!} Active
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class='well'>
                    <div class='form-actions'>
                        {!! HTML::link('admin/mailinglists', Lang::get('redminportal::buttons.cancel'), array('class' => 'btn btn-link'))!!}
                        {!! Form::submit(Lang::get('redminportal::buttons.create'), array('class' => 'btn btn-primary pull-right')) !!}
                    </div>
                </div>
            </div>
        </div>
    {!! Form::close() !!}
@stop

@section('footer')
@stop
