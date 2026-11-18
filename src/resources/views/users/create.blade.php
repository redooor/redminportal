@extends('redminportal::layouts.master')

@section('navbar-breadcrumb')
    <li class="nav-item"><a class="nav-link" href="{{ URL::to('admin/users') }}">{{ Lang::get('redminportal::menus.users') }}</a></li>
    <li class="nav-item"><span class="nav-link active">{{ Lang::get('redminportal::forms.create') }}</span></li>
@stop

@section('content')

    @include('redminportal::partials.errors')

	<form method="POST" action="{{ action('\Redooor\Redminportal\App\Http\Controllers\UserController@postStore') }}" role="form">
	@csrf
    <div class="row">
        <div class="col-lg-9">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">{{ Lang::get('redminportal::forms.create_user') }}</h4>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="first_name">{{ Lang::get('redminportal::forms.first_name') }}</label>
                        <input type="text" name="first_name" id="first_name" value="{{ old('first_name', null) }}" class="form-control" autofocus required>
                    </div>

                    <div class="mb-3">
                        <label for="last_name">{{ Lang::get('redminportal::forms.last_name') }}</label>
                        <input type="text" name="last_name" id="last_name" value="{{ old('last_name', null) }}" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label for="email">{{ Lang::get('redminportal::forms.email') }}</label>
                        <input type="email" name="email" id="email" value="{{ old('email', null) }}" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label for="password">{{ Lang::get('redminportal::forms.password') }}</label>
                        <input type="password" name="password" id="password" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label for="password_confirmation">{{ Lang::get('redminportal::forms.reenter_password') }}</label>
                        <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" required>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">{{ Lang::get('redminportal::forms.role') }}</h4>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <select name="role[]" id="role" class="form-select" multiple>@foreach ($roles as $k => $label)<option value="{{ $k }}" @selected(in_array((string) $k, array_map('strval', (array) old('role', null)), true))>{{ $label }}</option>@endforeach</select>
                    </div>
                </div>
                <div class="card-footer">
                    <p class="form-text">
                        * {{ trans('redminportal::messages.allow_select_multiple') }}<br>
                        * {{ trans('redminportal::messages.how_to_deselect_multiple') }}
                    </p>
                </div>
            </div>
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">{{ trans('redminportal::forms.permissions') }}</h4>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="permission-inherit">{{ trans('redminportal::forms.inherit') }}</label>
                        {!! Redminportal::form()->inputer('permission-inherit', old('permission-inherit'), ['class' => 'tagsinput']) !!}
                    </div>
                    <div class="mb-3">
                        <label for="permission-allow">{{ trans('redminportal::forms.allowed') }}</label>
                        {!! Redminportal::form()->inputer('permission-allow', old('permission-allow'), ['class' => 'tagsinput']) !!}
                    </div>
                    <div class="mb-3">
                        <label for="permission-deny">{{ trans('redminportal::forms.denied') }}</label>
                        {!! Redminportal::form()->inputer('permission-deny', old('permission-deny'), ['class' => 'tagsinput']) !!}
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
                    <a href="{{ URL::to('admin/users') }}" class="btn btn-link btn-sm">{{ Lang::get('redminportal::buttons.cancel') }}</a>
                    <input type="submit" value="{{ Lang::get('redminportal::buttons.create') }}" class="btn btn-primary btn-sm float-end">
                </div>
            </div>
            <div class="card card-body bg-body-tertiary">
                <div class="mb-3">
                    <div class="form-check">
                        <label class="form-check-label" for="activated-checker">
                            <input type="checkbox" name="activated" value="yes" id="activated-checker" class="form-check-input" @checked(session()->hasOldInput() ? old('activated') : true)> {{ Lang::get('redminportal::forms.activate_now') }}
                        </label>
                    </div>
                    <p class="form-text">{{ Lang::get('redminportal::messages.allow_user_to_login_this_account') }}</p>
                </div>
            </div>
        </div>
    </div>
	</form>
@stop

@section('footer')
    @include('redminportal::plugins/tagsinput-permission')
@stop
