@extends('redminportal::layouts.master')

@section('navbar-breadcrumb')
    <li><a href="{{ URL::to('admin/groups') }}">{{ Lang::get('redminportal::menus.groups') }}</a></li>
    <li class="active"><span class="navbar-text">{{ Lang::get('redminportal::forms.edit') }}</span></li>
@stop

@section('content')
    
    @include('redminportal::partials.errors')

	{!! Form::open(array('action' => '\Redooor\Redminportal\App\Http\Controllers\GroupController@postStore', 'role' => 'form')) !!}
    {!! Form::hidden('id', $group->id) !!}
    <div class="row">
        <div class="col-md-9">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4 class="panel-title">{{ Lang::get('redminportal::forms.edit_group') }}</h4>
                </div>
                <div class="panel-body">
                    <div class="form-group">
                        {!! Form::label('name', Lang::get('redminportal::forms.name')) !!}
                        {!! Form::text('name', $group->name, array('class' => 'form-control', 'autofocus', 'required')) !!}
                    </div>
                </div>
            </div>
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4 class="panel-title">{{ trans('redminportal::forms.permissions') }}</h4>
                </div>
                <div class="panel-body">
                    <div class="form-group">
                        <label for="permission-inherit">{{ trans('redminportal::forms.inherit') }}</label>
                        {!! Redminportal::form()->inputer('permission-inherit', $permission_inherit, ['class' => 'tagsinput']) !!}
                    </div>
                    <div class="form-group">
                        <label for="permission-allow">{{ trans('redminportal::forms.allowed') }}</label>
                        {!! Redminportal::form()->inputer('permission-allow', $permission_allow, ['class' => 'tagsinput']) !!}
                    </div>
                    <div class="form-group">
                        <label for="permission-deny">{{ trans('redminportal::forms.denied') }}</label>
                        {!! Redminportal::form()->inputer('permission-deny', $permission_deny, ['class' => 'tagsinput']) !!}
                    </div>
                </div>
                <div class="panel-footer">
                    <p class="help-block">{{ trans('redminportal::messages.help_permission_builder') }}</p>
                </div>
            </div>
            <div class="panel panel-info">
                <div class="panel-heading">
                    <h4 class="panel-title">{{ trans('redminportal::forms.permission_builder') }}</h4>
                </div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-4">
                            <label>{{ trans('redminportal::forms.route') }}*</label>
                            <select id="select-route" multiple class="form-control">
                                @foreach (config('redminportal::permissions.routes') as $route)
                                <option value="{{ $route['value'] }}">{{ isset($route['translate']) ? trans($route['translate']) : $route['name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label>{{ trans('redminportal::forms.action') }}*</label>
                            <select id="select-action" multiple class="form-control">
                                @foreach (config('redminportal::permissions.actions') as $action)
                                <option value="{{ $action['value'] }}">{{ isset($action['translate']) ? trans($action['translate']) : $action['name'] }}</option>
                                @endforeach
                            </select>

                        </div>
                        <div class="col-md-4">
                            <label>{{ trans('redminportal::forms.permission') }}</label>
                            <select id="select-permission" class="form-control">
                                @foreach (config('redminportal::permissions.permissions') as $permission)
                                <option value="{{ $permission['value'] }}">{{ isset($permission['translate']) ? trans($permission['translate']) : $permission['name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="panel-footer">
                    <div class="row">
                        <div class="col-md-8">
                            <p class="help-block">
                                * {{ trans('redminportal::messages.allow_select_multiple') }}<br>
                                * {{ trans('redminportal::messages.how_to_deselect_multiple') }}
                            </p>
                        </div>
                        <div class="col-md-4 text-right">
                            <a id="btn-add-permission" class="btn btn-primary btn-sm">{{ trans('redminportal::buttons.insert') }}</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
		<div class="col-md-3">
            <div class="well">
                <div class='form-actions'>
                    {!! HTML::link('admin/groups', Lang::get('redminportal::buttons.cancel'), array('class' => 'btn btn-link btn-sm'))!!}
                    {!! Form::submit(Lang::get('redminportal::buttons.save'), array('class' => 'btn btn-primary btn-sm pull-right')) !!}
                </div>
            </div>
        </div>
    </div>
		
	{!! Form::close() !!}
@stop

@section('footer')
    @include('redminportal::plugins/tagsinput-permission')
@stop
