@extends('redminportal::layouts.master')

@section('navbar-breadcrumb')
    <li><a href="{{ URL::to('admin/mailinglists') }}">{{ Lang::get('redminportal::menus.mailinglist') }}</a></li>
    <li class="active"><span class="navbar-text">{{ Lang::get('redminportal::forms.edit') }}</span></li>
@stop

@section('content')
    
    @include('redminportal::partials.errors')

    {!! Form::open(array('files' => TRUE, 'action' => '\Redooor\Redminportal\App\Http\Controllers\MailinglistController@postStore', 'role' => 'form')) !!}
        {!! Form::hidden('id', $mailinglist->id) !!}

        <div class="row">
            <div class="col-md-9">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h4 class="panel-title">{{ Lang::get('redminportal::forms.edit_mailinglist') }}</h4>
                    </div>
                    <div class="panel-body">
                        <div class="form-group">
                            {!! Form::label('first_name', Lang::get('redminportal::forms.first_name')) !!}
                            {!! Form::text('first_name', $mailinglist->first_name, array('class' => 'form-control', 'required', 'autofocus')) !!}
                        </div>

                        <div class="form-group">
                            {!! Form::label('last_name', Lang::get('redminportal::forms.last_name')) !!}
                            {!! Form::text('last_name', $mailinglist->last_name, array('class' => 'form-control', 'required')) !!}
                        </div>

                        <div class="form-group">
                            {!! Form::label('email', Lang::get('redminportal::forms.email')) !!}
                            {!! Form::email('email', $mailinglist->email, array('class' => 'form-control', 'required')) !!}
                        </div>
                        
                        <div class="well">
                            <div class="form-group">
                                <div class="checkbox">
                                    <label for="active-checker">
                                        {!! Form::checkbox('active', $mailinglist->active, $mailinglist->active, array('id' => 'active-checker')) !!} {{ Lang::get('redminportal::forms.active') }}
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
                        {!! HTML::link('admin/mailinglists', Lang::get('redminportal::buttons.cancel'), array('class' => 'btn btn-link btn-sm'))!!}
                        {!! Form::submit(Lang::get('redminportal::buttons.save'), array('class' => 'btn btn-primary btn-sm pull-right')) !!}
                    </div>
                </div>
            </div>
        </div>
    {!! Form::close() !!}
@stop

@section('footer')
@stop
