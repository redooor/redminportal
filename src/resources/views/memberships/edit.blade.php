@extends('redminportal::layouts.master')

@section('navbar-breadcrumb')
    <li class="nav-item"><a class="nav-link" href="{{ URL::to('admin/memberships') }}">{{ Lang::get('redminportal::menus.memberships') }}</a></li>
    <li class="nav-item"><span class="nav-link active">{{ Lang::get('redminportal::forms.edit') }}</span></li>
@stop

@section('content')
    
    @include('redminportal::partials.errors')

    {!! Form::open(array('files' => TRUE, 'action' => '\Redooor\Redminportal\App\Http\Controllers\MembershipController@postStore', 'role' => 'form')) !!}
        {!! Form::hidden('id', $membership->id)!!}

        <div class='row'>
            <div class="col-lg-9">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0">{{ Lang::get('redminportal::forms.edit_membership') }}</h4>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            {!! Form::label('name', Lang::get('redminportal::forms.name')) !!}
                            {!! Form::text('name', $membership->name, array('class' => 'form-control', 'required', 'autofocus')) !!}
                        </div>
                        <div class="mb-3">
                            {!! Form::label('rank', Lang::get('redminportal::forms.rank')) !!}
                            {!! Form::input('number', 'rank', $membership->rank, array('class' => 'form-control', 'min' => '0', 'required')) !!}
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3">
                <div class="card card-body bg-body-tertiary">
                    <div class='form-actions'>
                        {!! HTML::link('admin/memberships', Lang::get('redminportal::buttons.cancel'), array('class' => 'btn btn-link btn-sm'))!!}
                        {!! Form::submit(Lang::get('redminportal::buttons.save'), array('class' => 'btn btn-primary btn-sm float-end')) !!}
                    </div>
                </div>
            </div>
        </div>
    {!! Form::close() !!}
@stop

@section('footer')
@stop
