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

    {!! Form::open(array('files' => TRUE, 'action' => '\Redooor\Redminportal\App\Http\Controllers\MailinglistController@postStore', 'role' => 'form')) !!}
        {!! Form::hidden('id', $mailinglist->id) !!}

        <div class='row'>
            <div class="col-md-12">
                <div class='form-actions text-right'>
                    {!! HTML::link('admin/mailinglists', 'Cancel', array('class' => 'btn btn-default')) !!}
                    {!! Form::submit('Save Changes', array('class' => 'btn btn-primary')) !!}
                </div>
                <hr>
                <div class="form-group">
                    {!! Form::label('first_name', 'First Name') !!}
                    {!! Form::text('first_name', $mailinglist->first_name, array('class' => 'form-control', 'required', 'autofocus')) !!}
                </div>

                <div class="form-group">
                    {!! Form::label('last_name', 'Last Name') !!}
                    {!! Form::text('last_name', $mailinglist->last_name, array('class' => 'form-control', 'required')) !!}
                </div>

                <div class="form-group">
                    {!! Form::label('email', 'Email') !!}
                    {!! Form::email('email', $mailinglist->email, array('class' => 'form-control', 'required')) !!}
                </div>
                <div class='well well-small'>
                    <div class="form-group">
                        <div class="checkbox">
                            <label for="active-checker">
                                {!! Form::checkbox('active', $mailinglist->active, $mailinglist->active, array('id' => 'active-checker')) !!} Active
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    {!! Form::close() !!}
@stop

@section('footer')
@stop
