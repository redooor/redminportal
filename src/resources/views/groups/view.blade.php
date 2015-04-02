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

	<div class="nav-controls text-right">
		@if (count($groups) > 0)
		<span class="label label-default pull-left">
			{{ $groups->firstItem() . ' to ' . $groups->lastItem() . ' ( total ' . $groups->total() . ' )' }}
		</span>
		@endif
        <a href="{{ URL::to('admin/groups/create') }}" class="btn btn-primary">Create New</a>
	</div>

	@if (count($groups) > 0)
		<table class='table table-striped table-bordered'>
			<thead>
				<tr>
                    <th>
                        <a href="{{ URL::to('admin/groups/sort') . '/name/' . ($sortBy == 'name' && $orderBy == 'asc' ? 'desc' : 'asc') }}">
                            Name
                            @if ($sortBy == 'name')
                            {!! ($orderBy == 'asc' ? '<span class="caret"></span>' : '<span class="dropup"><span class="caret"></span></span>') !!}
                            @endif
                        </a>
                    </th>
					<th>Permissions</th>
					<th>
                        <a href="{{ URL::to('admin/groups/sort') . '/created_at/' . ($sortBy == 'created_at' && $orderBy == 'asc' ? 'desc' : 'asc') }}">
                            Created
                            @if ($sortBy == 'created_at')
                            {!! ($orderBy == 'asc' ? '<span class="caret"></span>' : '<span class="dropup"><span class="caret"></span></span>') !!}
                            @endif
                        </a>
                    </th>
					<th>
                        <a href="{{ URL::to('admin/groups/sort') . '/updated_at/' . ($sortBy == 'updated_at' && $orderBy == 'asc' ? 'desc' : 'asc') }}">
                            Updated
                            @if ($sortBy == 'updated_at')
                            {!! ($orderBy == 'asc' ? '<span class="caret"></span>' : '<span class="dropup"><span class="caret"></span></span>') !!}
                            @endif
                        </a>
                    </th>
                    <th>Action</th>
				</tr>
			</thead>
			<tbody>
				@foreach ($groups as $group)
				<tr>
			        <td>{{ $group->name }}</td>
			        <td>
                        @if ($group->permissions)
                            @foreach ($group->permissions() as $key => $value)
                                @if ($value == 1)
                                    <span class="label label-success">{{ $key }}</span>
                                @else
                                    <span class="label label-danger">{{ $key }}</span>
                                @endif
                            @endforeach
                        @endif
                    </td>
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
        <div class="text-center">
		{!! $groups->render() !!}
        </div>
	@else
		<div class="alert alert-info">No group found</div>
	@endif
@stop
