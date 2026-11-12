@extends('redminportal::layouts.master')

@section('navbar-breadcrumb')
    <li class="nav-item"><span class="nav-link active">{{ Lang::get('redminportal::menus.users') }}</span></li>
@stop

@section('content')
    @include('redminportal::partials.confirm-delete')

    @include('redminportal::partials.errors')
    
    <div class="row">
        <div class="col-lg-4">
            {!! Redminportal::form()->searchForm(url('admin/users'), url('admin/users/search'), $searchable_fields, (isset($field) ? $field : null), (isset($search) ? $search : null)) !!}
        </div>
        <div class="col-lg-8">
            <div class="nav-controls text-end">
                <div class="btn-group" role="group">
                    @if (count($models) > 0)
                    <a href="" class="btn btn-secondary btn-sm disabled btn-text">{{ $models->firstItem() . ' to ' . $models->lastItem() . ' of ' . $models->total() }}</a>
                    @endif
                    @permitted('admin/users/create')
                    <a href="{{ url('admin/users/create') }}" class="btn btn-primary btn-sm">{{ Lang::get('redminportal::buttons.create_new') }}</a>
                    @endpermitted
                </div>
            </div>
        </div>
    </div>

	@if (count($models) > 0)
		<div class="table-responsive">
		<table class='table table-striped table-sm'>
			<thead>
				<tr>
					<th>
                        {!! Redminportal::html()->sorter('admin/users', 'email', $sortBy, $orderBy) !!}
                    </th>
                    <th>
                        {!! Redminportal::html()->sorter('admin/users', 'first_name', $sortBy, $orderBy) !!}
                    </th>
                    <th>
                        {!! Redminportal::html()->sorter('admin/users', 'last_name', $sortBy, $orderBy) !!}
                    </th>
                    <th>
                        {!! Redminportal::html()->sorter('admin/users', 'group_name', $sortBy, $orderBy, trans('redminportal::forms.groups')) !!}
                    </th>
                    <th>
                        {!! Redminportal::html()->sorter('admin/users', 'activated', $sortBy, $orderBy) !!}
                    </th>
                    <th>
                        {!! Redminportal::html()->sorter('admin/users', 'last_login', $sortBy, $orderBy) !!}
                    </th>
                    <th>
                        {!! Redminportal::html()->sorter('admin/users', 'created_at', $sortBy, $orderBy) !!}
                    </th>
                    <th>
                        {!! Redminportal::html()->sorter('admin/users', 'updated_at', $sortBy, $orderBy) !!}
                    </th>
					<th></th>
				</tr>
			</thead>
			<tbody>
		    @foreach ($models as $user)
			    <tr>
			        <td>{{ $user->email }}</td>
			        <td>{{ $user->first_name }}</td>
			        <td>{{ $user->last_name }}</td>
			        <td>
						@foreach($user->groups as $group)
						<span class="badge text-bg-info">{{ $group->name }}</span>
						@endforeach
					</td>
			        <td>
			            @if ($user->activated)
			                 <span class="badge text-bg-success"><span class='fa-solid fa-check'></span></span>
                        @else
                            <span class="badge text-bg-danger"><span class='fa-solid fa-xmark'></span></span>
                        @endif
		            </td>
			        <td>@if ($user->last_login){{ date('d/m/y h:i A', strtotime($user->last_login)) }}@endif</td>
                    <td>{{ date("d/m/y h:i A", strtotime($user->created_at)) }}</td>
                    <td>{{ date("d/m/y h:i A", strtotime($user->updated_at)) }}</td>
					<td class="table-actions text-end">
                        @permitted('admin/users/deactivate/' . $user->id, 'admin/users/activate/' . $user->id, 'admin/users/edit/' . $user->id, 'admin/users/delete/' . $user->id)
                        <div class="btn-group">
                            <button type="button" class="btn btn-link dropdown-toggle" data-bs-toggle="dropdown" data-bs-popper-config='{"strategy":"fixed"}'>
								<i class="fa-solid fa-ellipsis"></i>
							</button>
							<ul class="dropdown-menu dropdown-menu-end">
								@if ($user->activated)
									@permitted('admin/users/deactivate/' . $user->id)
									<li>
										<a class="dropdown-item" href="{{ URL::to('admin/users/deactivate/' . $user->id) }}">
											<i class="fa-solid fa-eye-slash"></i>{{ Lang::get('redminportal::buttons.deactivate') }}</a>
									</li>
									@endpermitted
								@else
									@permitted('admin/users/activate/' . $user->id)
									<li>
										<a class="dropdown-item" href="{{ URL::to('admin/users/activate/' . $user->id) }}">
											<i class="fa-solid fa-eye"></i>{{ Lang::get('redminportal::buttons.activate') }}</a>
									</li>
									@endpermitted
								@endif
								@permitted('admin/users/edit/' . $user->id)
								<li>
									<a class="dropdown-item" href="{{ URL::to('admin/users/edit/' . $user->id) }}">
										<i class="fa-solid fa-pen-to-square"></i>{{ Lang::get('redminportal::buttons.edit') }}</a>
								</li>
								@endpermitted
                                @permitted('admin/users/delete/' . $user->id)
                                <li>
									<a class="dropdown-item btn-confirm" href="{{ URL::to('admin/users/delete/' . $user->id) }}">
										<i class="fa-solid fa-xmark"></i>{{ Lang::get('redminportal::buttons.delete') }}</a>
								</li>
                                @endpermitted
							</ul>
						</div>
                        @endpermitted
					</td>
		        </tr>
		    @endforeach
		    </tbody>
	    </table>
	    </div>
        <div class="text-center">
		{!! $models->render('pagination::bootstrap-5') !!}
        </div>
	@else
        @if ($models->total() > 0)
        <div class="p-3 rounded bg-body-secondary text-body-secondary">{{ Lang::get('redminportal::messages.no_record_page_empty') }}</div>
        <a href="{{ $models->url($models->lastPage()) }}" class="btn btn-secondary"><i class="fa-solid fa-chevron-left"></i> {{ Lang::get('redminportal::buttons.previous_page') }}</a>
        @else
        <div class="p-3 rounded bg-body-secondary text-body-secondary">{{ Lang::get('redminportal::messages.no_user_found') }}</div>
        @endif
	@endif
@stop

@section('footer')
<script>
    !function ($) {
        $(function(){
            $('[data-bs-toggle="tooltip"]').tooltip();
        })
    }(window.jQuery);
</script>
@stop
