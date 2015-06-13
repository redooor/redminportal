@extends('redminportal::layouts.master')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <ol class="breadcrumb">
                <li><a href="{{ URL::to('admin') }}">{{ Lang::get('redminportal::menus.home') }}</a></li>
                <li><a href="{{ URL::to('admin/memberships') }}">{{ Lang::get('redminportal::menus.memberships') }}</a></li>
                <li class="active">{{ Lang::get('redminportal::forms.edit') }}</li>
            </ol>
        </div>
    </div>
    
    @include('redminportal::partials.errors')

    {!! Form::open(array('files' => TRUE, 'action' => '\Redooor\Redminportal\App\Http\Controllers\MembershipController@postStore', 'role' => 'form')) !!}
        {!! Form::hidden('id', $membership->id)!!}

        <div class='row'>
            <div class="col-md-9">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h4 class="panel-title">{{ Lang::get('redminportal::forms.edit_membership') }}</h4>
                    </div>
                    <div class="panel-body">
                        <div class="form-group">
                            {!! Form::label('name', Lang::get('redminportal::forms.name')) !!}
                            {!! Form::text('name', $membership->name, array('class' => 'form-control', 'required', 'autofocus')) !!}
                        </div>
                        <div class="form-group">
                            {!! Form::label('rank', Lang::get('redminportal::forms.rank')) !!}
                            {!! Form::input('number', 'rank', $membership->rank, array('class' => 'form-control', 'min' => '0', 'required')) !!}
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="well">
                    <div class='form-actions'>
                        {!! HTML::link('admin/memberships', Lang::get('redminportal::buttons.cancel'), array('class' => 'btn btn-link btn-sm'))!!}
                        {!! Form::submit(Lang::get('redminportal::buttons.save'), array('class' => 'btn btn-primary btn-sm pull-right')) !!}
                    </div>
                </div>
            </div>
        </div>
    {!! Form::close() !!}
@stop

@section('footer')
@stop
