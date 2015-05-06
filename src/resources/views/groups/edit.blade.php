@extends('redminportal::layouts.master')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <ol class="breadcrumb">
                <li><a href="{{ URL::to('admin') }}">{{ Lang::get('redminportal::menus.home') }}</a></li>
                <li><a href="{{ URL::to('admin/groups') }}">{{ Lang::get('redminportal::menus.groups') }}</a></li>
                <li class="active">{{ Lang::get('redminportal::forms.edit') }}</li>
            </ol>
        </div>
    </div>
    
    @include('redminportal::partials.errors')

	{!! Form::open(array('action' => '\Redooor\Redminportal\App\Http\Controllers\GroupController@postStore', 'role' => 'form')) !!}
    {!! Form::hidden('id', $group->id) !!}
    <div class="row">
        <div class="col-md-9">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4 class="panel-title">{{ Lang::get('redminportal::forms.create_group') }}</h4>
                </div>
                <div class="panel-body">
                    <div class="form-group">
                        {!! Form::label('name', 'Name') !!}
                        {!! Form::text('name', $group->name, array('class' => 'form-control', 'autofocus', 'required')) !!}
                    </div>

                    <div class="form-group">
                        <div class="checkbox">
                            <label for="view">
                                {!! Form::checkbox('view', 'yes', $checkbox_view, array('id' => 'view')) !!} View
                            </label>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="checkbox">
                            <label for="create">
                                {!! Form::checkbox('create', 'yes', $checkbox_create, array('id' => 'create')) !!} Create
                            </label>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="checkbox">
                            <label for="delete">
                                {!! Form::checkbox('delete', 'yes', $checkbox_delete, array('id' => 'delete')) !!} Delete
                            </label>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="checkbox">
                            <label for="update">
                                {!! Form::checkbox('update', 'yes', $checkbox_update, array('id' => 'update')) !!} Update
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
		<div class="col-md-3">
            <div class="well">
                <div class='form-actions'>
                    {!! HTML::link('admin/groups', Lang::get('redminportal::buttons.cancel'), array('class' => 'btn btn-link'))!!}
                    {!! Form::submit(Lang::get('redminportal::buttons.save'), array('class' => 'btn btn-primary pull-right')) !!}
                </div>
            </div>
        </div>
    </div>
		
	{!! Form::close() !!}
@stop