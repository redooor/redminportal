@extends('redminportal::layouts.master')

@section('navbar-breadcrumb')
    <li class="nav-item"><a class="nav-link" href="{{ URL::to('admin/memberships') }}">{{ Lang::get('redminportal::menus.memberships') }}</a></li>
    <li class="nav-item"><span class="nav-link active">{{ Lang::get('redminportal::forms.edit') }}</span></li>
@stop

@section('content')
    
    @include('redminportal::partials.errors')

    <form method="POST" action="{{ action('\Redooor\Redminportal\App\Http\Controllers\MembershipController@postStore') }}" enctype="multipart/form-data" role="form">
        @csrf
        <input type="hidden" name="id" value="{{ old('id', $membership->id) }}">

        <div class='row'>
            <div class="col-lg-9">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0">{{ Lang::get('redminportal::forms.edit_membership') }}</h4>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="name">{{ Lang::get('redminportal::forms.name') }}</label>
                            <input type="text" name="name" id="name" value="{{ old('name', $membership->name) }}" class="form-control" required autofocus>
                        </div>
                        <div class="mb-3">
                            <label for="rank">{{ Lang::get('redminportal::forms.rank') }}</label>
                            <input type="number" name="rank" id="rank" value="{{ old('rank', $membership->rank) }}" class="form-control" min="0" required>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3">
                <div class="card card-body bg-body-tertiary">
                    <div class='form-actions'>
                        <a href="{{ URL::to('admin/memberships') }}" class="btn btn-link btn-sm">{{ Lang::get('redminportal::buttons.cancel') }}</a>
                        <input type="submit" value="{{ Lang::get('redminportal::buttons.save') }}" class="btn btn-primary btn-sm float-end">
                    </div>
                </div>
            </div>
        </div>
    </form>
@stop

@section('footer')
@stop
