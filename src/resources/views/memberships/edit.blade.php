@extends('redminportal::layouts.master')

@section('content')
    @if (isset($errors))
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
    @endif

    {!! Form::open(array('files' => TRUE, 'action' => '\Redooor\Redminportal\App\Http\Controllers\MembershipController@postStore', 'role' => 'form')) !!}
        {!! Form::hidden('id', $membership->id)!!}

        <div class='row'>
            <div class="col-md-12">
                <div class="form-group">
                    {!! Form::label('name', 'Name') !!}
                    {!! Form::text('name', $membership->name, array('class' => 'form-control', 'required', 'autofocus')) !!}
                </div>
                <div class="form-group">
                    {!! Form::label('rank', 'Rank') !!}
                    {!! Form::input('number', 'rank', $membership->rank, array('class' => 'form-control', 'min' => '0', 'required')) !!}
                </div>
                <hr>
                <div class='form-actions text-right'>
                    {!! HTML::link('admin/memberships', 'Cancel', array('class' => 'btn btn-default'))!!}
                    {!! Form::submit('Save Changes', array('class' => 'btn btn-primary')) !!}
                </div>
            </div>
        </div>
    {!! Form::close() !!}
@stop

@section('footer')
@stop
