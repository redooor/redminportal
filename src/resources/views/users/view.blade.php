@extends('redminportal::layouts.master')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <ol class="breadcrumb">
                <li><a href="{{ URL::to('admin') }}">{{ Lang::get('redminportal::menus.home') }}</a></li>
                <li class="active">{{ Lang::get('redminportal::menus.users') }}</li>
            </ol>
        </div>
    </div>

    @include('redminportal::partials.errors')
    
    <div class="row">
        <div class="col-md-12">
            <div class="nav-controls text-right">
                <div class="btn-group" role="group">
                @if (count($users) > 0)
                <a href="" class="btn btn-default btn-sm disabled btn-text">{{ $users->firstItem() . ' to ' . $users->lastItem() . ' of ' . $users->total() }}</a>
                @endif
                {!! HTML::link('admin/users/create', Lang::get('redminportal::buttons.create_new'), array('class' => 'btn btn-primary btn-sm')) !!}
            </div>
            </div>
        </div>
    </div>

	@if (count($users) > 0)
		<table class='table table-striped table-bordered table-condensed'>
			<thead>
				<tr>
					<th>
                        <a class="block-header" href="{{ URL::to('admin/users/sort') . '/email/' . ($sortBy == 'email' && $orderBy == 'asc' ? 'desc' : 'asc') }}">
                            Email
                            @if ($sortBy == 'email')
                            {!! ($orderBy == 'asc' ? '<span class="caret"></span>' : '<span class="dropup"><span class="caret"></span></span>') !!}
                            @endif
                        </a>
                    </th>
                    <th>
                        <a class="block-header" href="{{ URL::to('admin/users/sort') . '/first_name/' . ($sortBy == 'first_name' && $orderBy == 'asc' ? 'desc' : 'asc') }}">
                            First Name
                            @if ($sortBy == 'first_name')
                            {!! ($orderBy == 'asc' ? '<span class="caret"></span>' : '<span class="dropup"><span class="caret"></span></span>') !!}
                            @endif
                        </a>
                    </th>
                    <th>
                        <a class="block-header" href="{{ URL::to('admin/users/sort') . '/last_name/' . ($sortBy == 'last_name' && $orderBy == 'asc' ? 'desc' : 'asc') }}">
                            Last Name
                            @if ($sortBy == 'last_name')
                            {!! ($orderBy == 'asc' ? '<span class="caret"></span>' : '<span class="dropup"><span class="caret"></span></span>') !!}
                            @endif
                        </a>
                    </th>
                    <th>
                        <a class="block-header" href="{{ URL::to('admin/users/sort') . '/group/' . ($sortBy == 'group' && $orderBy == 'asc' ? 'desc' : 'asc') }}">
                            Groups
                            @if ($sortBy == 'group')
                            {!! ($orderBy == 'asc' ? '<span class="caret"></span>' : '<span class="dropup"><span class="caret"></span></span>') !!}
                            @endif
                        </a>
                    </th>
                    <th>
                        <a class="block-header" href="{{ URL::to('admin/users/sort') . '/activated/' . ($sortBy == 'activated' && $orderBy == 'asc' ? 'desc' : 'asc') }}">
                            Activated
                            @if ($sortBy == 'activated')
                            {!! ($orderBy == 'asc' ? '<span class="caret"></span>' : '<span class="dropup"><span class="caret"></span></span>') !!}
                            @endif
                        </a>
                    </th>
                    <th>
                        <a class="block-header" href="{{ URL::to('admin/users/sort') . '/last_login/' . ($sortBy == 'last_login' && $orderBy == 'asc' ? 'desc' : 'asc') }}">
                            Last Login
                            @if ($sortBy == 'last_login')
                            {!! ($orderBy == 'asc' ? '<span class="caret"></span>' : '<span class="dropup"><span class="caret"></span></span>') !!}
                            @endif
                        </a>
                    </th>
                    <th>
                        <a class="block-header" href="{{ URL::to('admin/users/sort') . '/created_at/' . ($sortBy == 'created_at' && $orderBy == 'asc' ? 'desc' : 'asc') }}">
                            Created
                            @if ($sortBy == 'created_at')
                            {!! ($orderBy == 'asc' ? '<span class="caret"></span>' : '<span class="dropup"><span class="caret"></span></span>') !!}
                            @endif
                        </a>
                    </th>
                    <th>
                        <a class="block-header" href="{{ URL::to('admin/users/sort') . '/updated_at/' . ($sortBy == 'updated_at' && $orderBy == 'asc' ? 'desc' : 'asc') }}">
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
		    @foreach ($users as $user)
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
			        <td>@if ($user->last_login){{ date('d-M-y g:i a', strtotime($user->last_login)) }}@endif</td>
			        <td>{{ date('d-M-y', strtotime($user->created_at)) }}</td>
			        <td>{{ date('d-M-y', strtotime($user->updated_at)) }}</td>
					<td>
						<div class="btn-group">
							<button type="button" class="btn btn-cyan btn-xs dropdown-toggle" data-toggle="dropdown">
								<span class="glyphicon glyphicon-th-list"></span>
							</button>
							<ul class="dropdown-menu pull-right" role="menu">
								@if ($user->activated)
									<li>
										<a href="{{ URL::to('admin/users/deactivate/' . $user->id) }}">
											<i class="glyphicon glyphicon-eye-close"></i>Deactivate</a>
									</li>
								@else
									<li>
										<a href="{{ URL::to('admin/users/activate/' . $user->id) }}">
											<i class="glyphicon glyphicon-eye-open"></i>Activate</a>
									</li>
								@endif
								<li>
									<a href="{{ URL::to('admin/users/edit/' . $user->id) }}">
										<i class="glyphicon glyphicon-edit"></i>Edit</a>
								</li>
                                <li>
									<a href="{{ URL::to('admin/users/delete/' . $user->id) }}" class="btn-confirm">
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
		{!! $users->render() !!}
        </div>
	@else
		<div class="alert alert-info">No user found</div>
	@endif
@stop
