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

	<div class="nav-controls text-right">
		@if (count($groups) > 0)
		<span class="label label-default pull-left">
			{{ $groups->getFrom() . ' to ' . $groups->getTo() . ' ( total ' . $groups->getTotal() . ' )' }}
		</span>
		@endif
		{{ HTML::link('admin/groups/create', 'Create New', array('class' => 'btn btn-primary')) }}
	</div>

	@if (count($groups) > 0)
		<table class='table table-striped table-bordered'>
			<thead>
				<tr>
					<th>Name</th>
					<th>Permissions</th>
					<th>Created</th>
					<th>Updated</th>
                    <th>Action</th>
				</tr>
			</thead>
			<tbody>
				@foreach ($groups as $group)
				<tr>
			        <td>{{ $group->name }}</td>
			        <td>{{ HTML::attributes($group->permissions) }}</td>
			        <td>{{ $group->created_at }}</td>
			        <td>{{ $group->updated_at }}</td>
                    <td>
                        <div class="btn-group">
                            <button type="button" class="btn btn-default btn-xs dropdown-toggle" data-toggle="dropdown">
                                Action <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu pull-right" role="menu">
                                <li>
                                    <a href="{{ URL::to('admin/groups/edit/' . $group->id) }}">
                                        <i class="glyphicon glyphicon-edit"></i>Edit</a>
                                </li>
                                <li>
                                    <a href="{{ URL::to('admin/groups/delete/' . $group->id) }}" class="btn-confirm">
                                        <i class="glyphicon glyphicon-remove"></i>Delete</a>
                                </li>
                            </ul>
                        </div>
                    </td>
			    </tr>
			    @endforeach
			</tbody>
	    </table>
		{{ $groups->links() }}
	@else
		<div class="alert alert-info">No group found</div>
	@endif
@stop
