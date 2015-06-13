@extends('redminportal::layouts.master')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <ol class="breadcrumb">
                <li><a href="{{ URL::to('admin') }}">{{ Lang::get('redminportal::menus.home') }}</a></li>
                <li class="active">{{ Lang::get('redminportal::menus.groups') }}</li>
            </ol>
        </div>
    </div>

    @include('redminportal::partials.errors')
    
    <div class="row">
        <div class="col-md-12">
            <div class="nav-controls text-right">
                <div class="btn-group" role="group">
                @if (count($groups) > 0)
                <a href="" class="btn btn-default btn-sm disabled btn-text">{{ $groups->firstItem() . ' to ' . $groups->lastItem() . ' of ' . $groups->total() }}</a>
                @endif
                {!! HTML::link('admin/groups/create', Lang::get('redminportal::buttons.create_new'), array('class' => 'btn btn-primary btn-sm')) !!}
            </div>
            </div>
        </div>
    </div>

	@if (count($groups) > 0)
		<table class='table table-striped table-bordered table-condensed'>
			<thead>
				<tr>
                    <th>
                        <a href="{{ URL::to('admin/groups/sort') . '/name/' . ($sortBy == 'name' && $orderBy == 'asc' ? 'desc' : 'asc') }}">
                            {{ Lang::get('redminportal::forms.name') }}
                            @if ($sortBy == 'name')
                            {!! ($orderBy == 'asc' ? '<span class="caret"></span>' : '<span class="dropup"><span class="caret"></span></span>') !!}
                            @endif
                        </a>
                    </th>
					<th>Permissions</th>
					<th>
                        <a href="{{ URL::to('admin/groups/sort') . '/created_at/' . ($sortBy == 'created_at' && $orderBy == 'asc' ? 'desc' : 'asc') }}">
                            {{ Lang::get('redminportal::forms.created') }}
                            @if ($sortBy == 'created_at')
                            {!! ($orderBy == 'asc' ? '<span class="caret"></span>' : '<span class="dropup"><span class="caret"></span></span>') !!}
                            @endif
                        </a>
                    </th>
					<th>
                        <a href="{{ URL::to('admin/groups/sort') . '/updated_at/' . ($sortBy == 'updated_at' && $orderBy == 'asc' ? 'desc' : 'asc') }}">
                            {{ Lang::get('redminportal::forms.updated') }}
                            @if ($sortBy == 'updated_at')
                            {!! ($orderBy == 'asc' ? '<span class="caret"></span>' : '<span class="dropup"><span class="caret"></span></span>') !!}
                            @endif
                        </a>
                    </th>
                    <th></th>
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
		{!! $groups->render() !!}
        </div>
	@else
		<div class="alert alert-info">{{ Lang::get('redminportal::messages.no_group_found') }}</div>
	@endif
@stop
