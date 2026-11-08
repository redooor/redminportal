@extends('redminportal::layouts.master')

@section('navbar-breadcrumb')
    <li class="nav-item"><span class="nav-link active">{{ Lang::get('redminportal::menus.groups') }}</span></li>
@stop

@section('content')

    @include('redminportal::partials.errors')
    
    @include('redminportal::partials.html-view-nav-controls', ['models' => $models, 'model_view' => 'groups'])

	@if (count($models) > 0)
		<table class='table table-striped table-sm'>
			<thead>
				<tr>
                    <th>
                        {!! Redminportal::html()->sorter('admin/groups', 'name', $sortBy, $orderBy) !!}
                    </th>
					<th>Permissions</th>
					<th>
                        {!! Redminportal::html()->sorter('admin/groups', 'created_at', $sortBy, $orderBy) !!}
                    </th>
					<th>
                        {!! Redminportal::html()->sorter('admin/groups', 'updated_at', $sortBy, $orderBy) !!}
                    </th>
                    <th></th>
				</tr>
			</thead>
			<tbody>
				@foreach ($models as $group)
				<tr>
			        <td>{{ $group->name }}</td>
			        <td>
                        @if ($group->permissions)
                            @foreach ($group->permissions() as $key => $value)
                                @if ($value == 1)
                                    <span class="badge text-bg-success">{{ $key }}</span>
                                @elseif ($value == -1)
                                    <span class="badge text-bg-danger">{{ $key }}</span>
                                @else
                                    <span class="badge text-bg-secondary">{{ $key }}</span>
                                @endif
                            @endforeach
                        @endif
                    </td>
                    <td>{{ date("d/m/y h:i A", strtotime($group->created_at)) }}</td>
                    <td>{{ date("d/m/y h:i A", strtotime($group->updated_at)) }}</td>
                    <td class="table-actions text-end">
                        <div class="btn-group">
                            <button type="button" class="btn btn-link dropdown-toggle" data-bs-toggle="dropdown">
								<i class="fa-solid fa-ellipsis"></i>
							</button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item" href="{{ URL::to('admin/groups/edit/' . $group->id) }}">
                                        <i class="fa-solid fa-pen-to-square"></i>{{ Lang::get('redminportal::buttons.edit') }}</a>
                                </li>
                                <li>
                                    <a class="dropdown-item btn-confirm" href="{{ URL::to('admin/groups/delete/' . $group->id) }}">
                                        <i class="fa-solid fa-xmark"></i>{{ Lang::get('redminportal::buttons.delete') }}</a>
                                </li>
                            </ul>
                        </div>
                    </td>
			    </tr>
			    @endforeach
			</tbody>
	    </table>
        <div class="text-center">
		{!! $models->render('pagination::bootstrap-5') !!}
        </div>
	@else
        @if ($models->total() > 0)
        <div class="p-3 rounded bg-body-secondary text-body-secondary">{{ Lang::get('redminportal::messages.no_record_page_empty') }}</div>
        <a href="{{ $models->url($models->lastPage()) }}" class="btn btn-secondary"><i class="fa-solid fa-chevron-left"></i> {{ Lang::get('redminportal::buttons.previous_page') }}</a>
        @else
        <div class="p-3 rounded bg-body-secondary text-body-secondary">{{ Lang::get('redminportal::messages.no_group_found') }}</div>
        @endif
	@endif
@stop
