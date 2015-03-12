@extends('redminportal::layouts.master')

@section('content')
    @if($errors->has())
    <div class='alert alert-danger'>
        We encountered the following errors:
        <ul>
            @foreach($errors->all() as $message)
            <li>{{ $message }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    {{ Form::open(array('files' => TRUE, 'action' => 'Redooor\Redminportal\MembershipController@postStore', 'role' => 'form')) }}

        <div class='row'>
            <div class="col-md-12">
                <div class="form-group">
                    {{ Form::label('name', 'Name') }}
                    {{ Form::text('name', Input::old('name'), array('class' => 'form-control', 'required', 'autofocus')) }}
                </div>
                <div class="form-group">
                    {{ Form::label('rank', 'Rank') }}
                    {{ Form::input('number', 'rank', Input::old('rank')|'0', array('class' => 'form-control', 'min' => '0', 'required')) }}
                </div>
                <hr>
                <div class='form-actions text-right'>
                    {{ HTML::link('admin/memberships', 'Cancel', array('class' => 'btn btn-default'))}}
                    {{ Form::submit('Create', array('class' => 'btn btn-primary')) }}
                </div>
            </div>
        </div>
    {{ Form::close() }}
@stop

@section('footer')
@stop
