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

	{!! Form::open(array('action' => '\Redooor\Redminportal\App\Http\Controllers\GroupController@postStore', 'role' => 'form')) !!}
    {!! Form::hidden('id', $group->id) !!}
		<div class="form-group">
			{!! Form::label('name', 'Name') !!}
			{!! Form::text('name', $group->name, array('class' => 'form-control', 'required')) !!}
		</div>
		
		<div class="form-group">
            <div class="checkbox">
                <label for="view">
                    {!! Form::checkbox('view', 'yes', $checkbox_view, array('id' => 'view')) !!} View
                </label>
            </div>
		</div>
		
		<div class="form-group">
            <div class="checkbox">
                <label for="create">
                    {!! Form::checkbox('create', 'yes', $checkbox_create, array('id' => 'create')) !!} Create
                </label>
            </div>
		</div>

        <div class="form-group">
            <div class="checkbox">
                <label for="delete">
                    {!! Form::checkbox('delete', 'yes', $checkbox_delete, array('id' => 'delete')) !!} Delete
                </label>
            </div>
		</div>

        <div class="form-group">
            <div class="checkbox">
                <label for="update">
                    {!! Form::checkbox('update', 'yes', $checkbox_update, array('id' => 'update')) !!} Update
                </label>
            </div>
		</div>
		
		<div class='form-actions text-right'>
			{!! HTML::link('admin/groups', 'Cancel', array('class' => 'btn btn-default'))!!}
			{!! Form::submit('Save Changes', array('class' => 'btn btn-primary')) !!}
		</div>
		
	{!! Form::close() !!}
@stop