@extends('redminportal::layouts.plain')

@section('content')
    <div class="alert alert-danger">
        <p>{{ trans('redminportal::messages.not_authorized_to_view_this_page') }}</p>
    </div>
    <a href="{{ url('/') }}" class="btn btn-secondary"><i class="fa-solid fa-house"></i> {{ trans('redminportal::menus.home') }}</a>
    @if(Auth::guard('redminguard')->check())
    <a href="{{ url('admin/dashboard') }}" class="btn btn-secondary">{{ trans('redminportal::menus.dashboard') }}</a>
    @endif
@stop