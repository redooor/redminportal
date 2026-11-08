@extends('redminportal::layouts.master')

@section('navbar-breadcrumb')
    <li class="nav-item"><span class="nav-link active">{{ Lang::get('redminportal::menus.announcements') }}</span></li>
@stop

@section('content')

    @include('redminportal::partials.errors')
    
    @include('redminportal::partials.html-view-nav-controls', ['models' => $models, 'model_view' => 'announcements'])

    @if (count($models) >0)
        <table class='table table-striped table-sm'>
            <thead>
                <tr>
                    <th>{!! Redminportal::html()->sorter('admin/announcements', 'title', $sortBy, $orderBy) !!}</th>
                    <th>{!! Redminportal::html()->sorter('admin/announcements', 'created_at', $sortBy, $orderBy) !!}</th>
                    <th>{!! Redminportal::html()->sorter('admin/announcements', 'updated_at', $sortBy, $orderBy) !!}</th>
                    <th>{!! Redminportal::html()->sorter('admin/announcements', 'private', $sortBy, $orderBy) !!}</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            @foreach ($models as $announcement)
                <tr>
                    <td>{{ $announcement->title }}</td>
                    <td>{{ date('d/m/y h:i A', strtotime($announcement->created_at)) }}</td>
                    <td>{{ date('d/m/y h:i A', strtotime($announcement->updated_at)) }}</td>
                    <td>
                        @if ($announcement->private)
                            <span class="badge text-bg-success"><span class='fa-solid fa-check'></span></span>
                        @else
                            <span class="badge text-bg-danger"><span class='fa-solid fa-xmark'></span></span>
                        @endif
                    </td>
                    <td class="table-actions text-end">
                        <div class="btn-group">
                            <button type="button" class="btn btn-link dropdown-toggle" data-bs-toggle="dropdown">
								<i class="fa-solid fa-ellipsis"></i>
							</button>
						  	<ul class="dropdown-menu dropdown-menu-end">
						        <li>
						            <a class="dropdown-item" href="{{ URL::to('admin/announcements/edit/' . $announcement->id) }}">
						                <i class="fa-solid fa-pen-to-square"></i>{{ Lang::get('redminportal::buttons.edit') }}</a>
						        </li>
						        <li>
						            <a class="dropdown-item btn-confirm" href="{{ URL::to('admin/announcements/delete/' . $announcement->id) }}">
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
        <div class="p-3 rounded bg-body-secondary text-body-secondary">{{ Lang::get('redminportal::messages.no_announcement_found') }}</div>
        @endif
    @endif
@stop
