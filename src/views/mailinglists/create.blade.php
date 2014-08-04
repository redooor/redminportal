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

    {{ Form::open(array('files' => TRUE, 'action' => 'Redooor\Redminportal\MailinglistController@postStore', 'role' => 'form')) }}

        <div class='row'>
            <div class="col-md-12">
                <div class='form-actions text-right'>
                    {{ HTML::link('admin/mailinglists', 'Cancel', array('class' => 'btn btn-default'))}}
                    {{ Form::submit('Create', array('class' => 'btn btn-primary')) }}
                </div>
                <hr>
                <div class="form-group">
                    {{ Form::label('first_name', 'First Name') }}
                    {{ Form::text('first_name', null, array('class' => 'form-control', 'required', 'autofocus')) }}
                </div>

                <div class="form-group">
                    {{ Form::label('last_name', 'Last Name') }}
                    {{ Form::text('last_name', null, array('class' => 'form-control', 'required')) }}
                </div>

                <div class="form-group">
                    {{ Form::label('email', 'Email') }}
                    {{ Form::email('email', null, array('class' => 'form-control', 'required')) }}
                </div>
                <div class='well well-small'>
                    <div class="form-group">
                        <label for="active" class="checkbox inline">
                            {{ Form::checkbox('active', true, true, array('id' => 'active-checker')) }} Active
                        </label>
                    </div>
                </div>
            </div>
        </div>
    {{ Form::close() }}
@stop

@section('footer')
@stop
