@extends('redminportal::layouts.master')

@section('navbar-breadcrumb')
    <li class="active"><span class="navbar-text">{{ Lang::get('redminportal::menus.users') }}</span></li>
@stop

@section('content')

    @include('redminportal::partials.errors')
    
    <div class="row">
        <div class="col-md-4">
            {!! Redminportal::form()->searchForm(url('admin/users'), url('admin/users/search'), $searchable_fields, (isset($field) ? $field : null), (isset($search) ? $search : null)) !!}
        </div>
        <div class="col-md-8">
            <div class="nav-controls text-right">
                <div class="btn-group" role="group">
                    @if (count($models) > 0)
                    <a href="" class="btn btn-default btn-sm disabled btn-text">{{ $models->firstItem() . ' to ' . $models->lastItem() . ' of ' . $models->total() }}</a>
                    @endif
                    <a href="{{ url('admin/users/create') }}" class="btn btn-primary btn-sm">{{ Lang::get('redminportal::buttons.create_new') }}</a>
                </div>
            </div>
        </div>
    </div>

	@if (count($models) > 0)
		<table class='table table-striped table-bordered table-condensed'>
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
						<span class="label label-info">{{ $group->name }}</span>
						@endforeach
					</td>
			        <td>
			            @if ($user->activated)
			                 <span class="label label-success"><span class='glyphicon glyphicon-ok'></span></span>
                        @else
                            <span class="label label-danger"><span class='glyphicon glyphicon-remove'></span></span>
                        @endif
		            </td>
			        <td>@if ($user->last_login){{ date('d/m/y h:i A', strtotime($user->last_login)) }}@endif</td>
                    <td>{{ date("d/m/y h:i A", strtotime($user->created_at)) }}</td>
                    <td>{{ date("d/m/y h:i A", strtotime($user->updated_at)) }}</td>
					<td class="table-actions text-right">
                        <div class="btn-group">
                            <button type="button" class="btn btn-link dropdown-toggle" data-toggle="dropdown">
								<span class="glyphicon glyphicon-option-horizontal"></span>
							</button>
							<ul class="dropdown-menu pull-right" role="menu">
								@if ($user->activated)
									<li>
										<a href="{{ URL::to('admin/users/deactivate/' . $user->id) }}">
											<i class="glyphicon glyphicon-eye-close"></i>{{ Lang::get('redminportal::buttons.deactivate') }}</a>
									</li>
								@else
									<li>
										<a href="{{ URL::to('admin/users/activate/' . $user->id) }}">
											<i class="glyphicon glyphicon-eye-open"></i>{{ Lang::get('redminportal::buttons.activate') }}</a>
									</li>
								@endif
								<li>
									<a href="{{ URL::to('admin/users/edit/' . $user->id) }}">
										<i class="glyphicon glyphicon-edit"></i>{{ Lang::get('redminportal::buttons.edit') }}</a>
								</li>
                                <li>
									<a href="{{ URL::to('admin/users/delete/' . $user->id) }}" class="btn-confirm">
										<i class="glyphicon glyphicon-remove"></i>{{ Lang::get('redminportal::buttons.delete') }}</a>
								</li>
							</ul>
						</div>
					</td>
		        </tr>
		    @endforeach
		    </tbody>
	    </table>
        <div class="text-center">
		{!! $models->render() !!}
        </div>
	@else
        @if ($models->lastPage())
        <div class="alert alert-info">{{ Lang::get('redminportal::messages.no_record_page_empty') }}</div>
        <a href="{{ $models->url($models->lastPage()) }}" class="btn btn-default"><span class="glyphicon glyphicon-menu-left"></span> {{ Lang::get('redminportal::buttons.previous_page') }}</a>
        @else
        <div class="alert alert-info">{{ Lang::get('redminportal::messages.no_user_found') }}</div>
        @endif
	@endif
@stop

@section('footer')
<script>
    !function ($) {
        $(function(){
            $('[data-toggle="tooltip"]').tooltip();
        })
    }(window.jQuery);
</script>
@stop
