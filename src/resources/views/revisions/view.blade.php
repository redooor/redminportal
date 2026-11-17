@extends('redminportal::layouts.master')

@section('content')

    @include('redminportal::partials.errors')

    @include('redminportal::revisions.table')

@stop
