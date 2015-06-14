@extends('redminportal::layouts.plain')

@section('content')
    <div class="alert alert-danger">
        <p>{{ Lang::get('redminportal::messages.not_authorized_to_view_this_page') }}</p>
    </div>
@stop