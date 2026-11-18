@extends('redminportal::layouts.master')

@section('navbar-breadcrumb')
    <li class="nav-item"><a class="nav-link" href="{{ URL::to('admin/groups') }}">{{ Lang::get('redminportal::menus.groups') }}</a></li>
    <li class="nav-item"><span class="nav-link active">{{ Lang::get('redminportal::forms.edit') }}</span></li>
@stop

@section('content')
    
    @include('redminportal::partials.errors')

	<form method="POST" action="{{ action('\Redooor\Redminportal\App\Http\Controllers\GroupController@postStore') }}" role="form">
	@csrf
    <input type="hidden" name="id" value="{{ old('id', $group->id) }}">
    <div class="row">
        <div class="col-lg-9">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">{{ Lang::get('redminportal::forms.edit_group') }}</h4>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="name">{{ Lang::get('redminportal::forms.name') }}</label>
                        <input type="text" name="name" id="name" value="{{ old('name', $group->name) }}" class="form-control" autofocus required>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">{{ trans('redminportal::forms.permissions') }}</h4>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="permission-inherit">{{ trans('redminportal::forms.inherit') }}</label>
                        {!! Redminportal::form()->inputer('permission-inherit', $permission_inherit, ['class' => 'tagsinput']) !!}
                    </div>
                    <div class="mb-3">
                        <label for="permission-allow">{{ trans('redminportal::forms.allowed') }}</label>
                        {!! Redminportal::form()->inputer('permission-allow', $permission_allow, ['class' => 'tagsinput']) !!}
                    </div>
                    <div class="mb-3">
                        <label for="permission-deny">{{ trans('redminportal::forms.denied') }}</label>
                        {!! Redminportal::form()->inputer('permission-deny', $permission_deny, ['class' => 'tagsinput']) !!}
                    </div>
                </div>
                <div class="card-footer">
                    <p class="form-text">{{ trans('redminportal::messages.help_permission_builder') }}</p>
                </div>
            </div>
            <div class="card border-info">
                <div class="card-header">
                    <h4 class="card-title mb-0">{{ trans('redminportal::forms.permission_builder') }}</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-4">
                            <label>{{ trans('redminportal::forms.route') }}*</label>
                            <select id="select-route" multiple class="form-control">
                                @foreach (config('redminportal::permissions.routes') as $route)
                                <option value="{{ $route['value'] }}">{{ isset($route['translate']) ? trans($route['translate']) : $route['name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-4">
                            <label>{{ trans('redminportal::forms.action') }}*</label>
                            <select id="select-action" multiple class="form-control">
                                @foreach (config('redminportal::permissions.actions') as $action)
                                <option value="{{ $action['value'] }}">{{ isset($action['translate']) ? trans($action['translate']) : $action['name'] }}</option>
                                @endforeach
                            </select>

                        </div>
                        <div class="col-lg-4">
                            <label>{{ trans('redminportal::forms.permission') }}</label>
                            <select id="select-permission" class="form-control">
                                @foreach (config('redminportal::permissions.permissions') as $permission)
                                <option value="{{ $permission['value'] }}">{{ isset($permission['translate']) ? trans($permission['translate']) : $permission['name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="row">
                        <div class="col-lg-8">
                            <p class="form-text">
                                * {{ trans('redminportal::messages.allow_select_multiple') }}<br>
                                * {{ trans('redminportal::messages.how_to_deselect_multiple') }}
                            </p>
                        </div>
                        <div class="col-lg-4 text-end">
                            <a id="btn-add-permission" class="btn btn-primary btn-sm">{{ trans('redminportal::buttons.insert') }}</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
		<div class="col-lg-3">
            <div class="card card-body bg-body-tertiary">
                <div class='form-actions'>
                    <a href="{{ URL::to('admin/groups') }}" class="btn btn-link btn-sm">{{ Lang::get('redminportal::buttons.cancel') }}</a>
                    <input type="submit" value="{{ Lang::get('redminportal::buttons.save') }}" class="btn btn-primary btn-sm float-end">
                </div>
            </div>
        </div>
    </div>
		
	</form>
@stop

@section('footer')
    @include('redminportal::plugins/tagsinput-permission')
@stop
