@extends('redminportal::layouts.master')

@section('content')
	<div class="nav-controls text-right">
		{{ HTML::link('admin/groups/create', 'Create New', array('class' => 'btn btn-primary')) }}
	</div>
	
	@if ($groups)
		<table class='table table-striped table-bordered'>
			<thead>
				<tr>
					<th>Name</th>
					<th>Permissions</th>
					<th>Created</th>
					<th>Updated</th>
				</tr>
			</thead>
			<tbody>
				@foreach ($groups as $group)
				<tr>
			        <td>{{ $group->name }}</td>
			        <td>{{ HTML::attributes($group->permissions) }}</td>
			        <td>{{ $group->created_at }}</td>
			        <td>{{ $group->updated_at }}</td>
			    </tr>
			    @endforeach
			</tbody>
	    </table>
	@else
		<p>No group found</p>
	@endif
@stop