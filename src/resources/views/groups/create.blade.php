@extends('redminportal::layouts.master')

@section('navbar-breadcrumb')
    <li><a href="{{ URL::to('admin/groups') }}">{{ Lang::get('redminportal::menus.groups') }}</a></li>
    <li class="active"><span class="navbar-text">{{ Lang::get('redminportal::forms.create') }}</span></li>
@stop

@section('content')
    
    @include('redminportal::partials.errors')

	{!! Form::open(array('action' => '\Redooor\Redminportal\App\Http\Controllers\GroupController@postStore', 'role' => 'form')) !!}
	<div class="row">
        <div class="col-md-9">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4 class="panel-title">{{ Lang::get('redminportal::forms.create_group') }}</h4>
                </div>
                <div class="panel-body">
                    <div class="form-group">
                        {!! Form::label('name', Lang::get('redminportal::forms.name')) !!}
                        {!! Form::text('name', null, array('class' => 'form-control', 'autofocus', 'required')) !!}
                    </div>
                    
                    <div class="well">
                        <div class="form-group">
                            <div class="checkbox">
                                <label for="view">
                                    {!! Form::checkbox('view', 'yes', true, array('id' => 'view')) !!} {{ Lang::get('redminportal::forms.view') }}
                                </label>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="checkbox">
                                <label for="create">
                                    {!! Form::checkbox('create', 'no', false, array('id' => 'create')) !!} {{ Lang::get('redminportal::forms.create') }}
                                </label>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="checkbox">
                                <label for="delete">
                                    {!! Form::checkbox('delete', 'no', false, array('id' => 'delete')) !!} {{ Lang::get('redminportal::forms.delete') }}
                                </label>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="checkbox">
                                <label for="update">
                                    {!! Form::checkbox('update', 'no', false, array('id' => 'update')) !!} {{ Lang::get('redminportal::forms.update') }}
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
		<div class="col-md-3">
            <div class="well">
                <div class='form-actions'>
                    {!! HTML::link('admin/groups', Lang::get('redminportal::buttons.cancel'), array('class' => 'btn btn-link btn-sm'))!!}
                    {!! Form::submit(Lang::get('redminportal::buttons.create'), array('class' => 'btn btn-primary btn-sm pull-right')) !!}
                </div>
            </div>
        </div>
    </div>
		
	{!! Form::close() !!}
@stop