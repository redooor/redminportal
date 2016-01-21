@extends('redminportal::layouts.master')

@section('navbar-breadcrumb')
    <li class="active"><span class="navbar-text">{{ Lang::get('redminportal::menus.groups') }}</span></li>
@stop

@section('content')

    @include('redminportal::partials.errors')
    
    <div class="row">
        <div class="col-md-12">
            <div class="nav-controls text-right">
                <div class="btn-group" role="group">
                @if (count($models) > 0)
                <a href="" class="btn btn-default btn-sm disabled btn-text">{{ $models->firstItem() . ' to ' . $models->lastItem() . ' of ' . $models->total() }}</a>
                @endif
                {!! HTML::link('admin/groups/create', Lang::get('redminportal::buttons.create_new'), array('class' => 'btn btn-primary btn-sm')) !!}
            </div>
            </div>
        </div>
    </div>

	@if (count($models) > 0)
		<table class='table table-striped table-bordered table-condensed'>
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
                                    <span class="label label-success">{{ $key }}</span>
                                @else
                                    <span class="label label-danger">{{ $key }}</span>
                                @endif
                            @endforeach
                        @endif
                    </td>
                    <td>{{ date("d/m/y h:i A", strtotime($group->created_at)) }}</td>
                    <td>{{ date("d/m/y h:i A", strtotime($group->updated_at)) }}</td>
                    <td class="table-actions text-right">
                        <div class="btn-group">
                            <button type="button" class="btn btn-link dropdown-toggle" data-toggle="dropdown">
								<span class="glyphicon glyphicon-option-horizontal"></span>
							</button>
                            <ul class="dropdown-menu pull-right" role="menu">
                                <li>
                                    <a href="{{ URL::to('admin/groups/edit/' . $group->id) }}">
                                        <i class="glyphicon glyphicon-edit"></i>{{ Lang::get('redminportal::buttons.edit') }}</a>
                                </li>
                                <li>
                                    <a href="{{ URL::to('admin/groups/delete/' . $group->id) }}" class="btn-confirm">
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
        <div class="alert alert-info">{{ Lang::get('redminportal::messages.no_group_found') }}</div>
        @endif
	@endif
@stop
