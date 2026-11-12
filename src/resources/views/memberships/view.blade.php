@extends('redminportal::layouts.master')

@section('navbar-breadcrumb')
    <li class="nav-item"><span class="nav-link active">{{ Lang::get('redminportal::menus.memberships') }}</span></li>
@stop

@section('content')
    @include('redminportal::partials.confirm-delete')

    @include('redminportal::partials.errors')
    
    @include('redminportal::partials.html-view-nav-controls', ['models' => $models, 'model_view' => 'memberships'])

    @if (count($models) > 0)
        <div class="table-responsive">
        <table class='table table-striped table-sm'>
            <thead>
                <tr>
                    <th>{!! Redminportal::html()->sorter('admin/memberships', 'rank', $sortBy, $orderBy) !!}</th>
                    <th>{!! Redminportal::html()->sorter('admin/memberships', 'name', $sortBy, $orderBy) !!}</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            @foreach ($models as $membership)
                <tr>
                    <td>{{ $membership->rank }}</td>
                    <td>{{ $membership->name }}</td>
                    <td class="table-actions text-end">
                        @permitted('admin/memberships/edit/' . $membership->id, 'admin/memberships/delete/' . $membership->id)
                        <div class="btn-group">
                            <button type="button" class="btn btn-link dropdown-toggle" data-bs-toggle="dropdown" data-bs-popper-config='{"strategy":"fixed"}'>
								<i class="fa-solid fa-ellipsis"></i>
							</button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                @permitted('admin/memberships/edit/' . $membership->id)
                                <li>
                                    <a class="dropdown-item" href="{{ URL::to('admin/memberships/edit/' . $membership->id) }}">
                                        <i class="fa-solid fa-pen-to-square"></i>{{ Lang::get('redminportal::buttons.edit') }}</a>
                                </li>
                                @endpermitted
                                @permitted('admin/memberships/delete/' . $membership->id)
                                <li>
                                    <a class="dropdown-item btn-confirm" href="{{ URL::to('admin/memberships/delete/' . $membership->id) }}">
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
        <div class="p-3 rounded bg-body-secondary text-body-secondary">{{ Lang::get('redminportal::messages.no_membership_found') }}</div>
        @endif
    @endif
@stop
