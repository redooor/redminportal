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

	{!! Form::open(array('action' => '\Redooor\Redminportal\App\Http\Controllers\GroupController@postStore', 'role' => 'form')) !!}
	
		<div class="form-group">
			{!! Form::label('name', 'Name') !!}
			{!! Form::text('name', null, array('class' => 'form-control', 'autofocus', 'required')) !!}
		</div>
		
        <div class="form-group">
            <div class="checkbox">
                <label for="view">
                    {!! Form::checkbox('view', 'yes', true, array('id' => 'view')) !!} View
                </label>
            </div>
		</div>

		<div class="form-group">
            <div class="checkbox">
                <label for="create">
                    {!! Form::checkbox('create', 'no', false, array('id' => 'create')) !!} Create
                </label>
            </div>
		</div>
		
		<div class="form-group">
            <div class="checkbox">
                <label for="delete">
                    {!! Form::checkbox('delete', 'no', false, array('id' => 'delete')) !!} Delete
                </label>
            </div>
		</div>

        <div class="form-group">
            <div class="checkbox">
                <label for="update">
                    {!! Form::checkbox('update', 'no', false, array('id' => 'update')) !!} Update
                </label>
            </div>
		</div>
		
		<div class='form-actions text-right'>
			{!! HTML::link('admin/groups', 'Cancel', array('class' => 'btn btn-default'))!!}
			{!! Form::submit('Create', array('class' => 'btn btn-primary')) !!}
		</div>
		
	{!! Form::close() !!}
@stop