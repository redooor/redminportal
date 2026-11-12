@extends('redminportal::layouts.master')

@section('navbar-breadcrumb')
    <li class="nav-item"><span class="nav-link active">{{ Lang::get('redminportal::menus.promotions') }}</span></li>
@stop

@section('content')
    @include('redminportal::partials.confirm-delete')

    @include('redminportal::partials.errors')
    
    @include('redminportal::partials.html-view-nav-controls', ['models' => $models, 'model_view' => 'promotions'])
    
    @if (count($models) > 0)
        <div class="table-responsive">
        <table class='table table-striped table-sm'>
            <thead>
                <tr>
                    <th>{!! Redminportal::html()->sorter('admin/promotions', 'name', $sortBy, $orderBy) !!}</th>
                    <th>{!! Redminportal::html()->sorter('admin/promotions', 'start_date', $sortBy, $orderBy) !!}</th>
                    <th>{!! Redminportal::html()->sorter('admin/promotions', 'end_date', $sortBy, $orderBy) !!}</th>
                    <th>{!! Redminportal::html()->sorter('admin/promotions', 'active', $sortBy, $orderBy) !!}</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            @foreach ($models as $promotion)
                <tr>
                    <td>
                        {{ $promotion->name }}
                    </td>
                    <td>{{ date('d/m/y h:i A', strtotime($promotion->start_date)) }}</td>
                    <td>{{ date('d/m/y h:i A', strtotime($promotion->end_date)) }}</td>
                    <td>
                        @if ($promotion->active)
                            <span class="badge text-bg-success"><span class='fa-solid fa-check'></span></span>
                        @else
                            <span class="badge text-bg-danger"><span class='fa-solid fa-xmark'></span></span>
                        @endif
                    </td>
                    <td class="table-actions text-end">
                        @permitted('admin/promotions/edit/' . $promotion->id, 'admin/promotions/delete/' . $promotion->id)
                        <div class="btn-group">
                            <button type="button" class="btn btn-link dropdown-toggle" data-bs-toggle="dropdown" data-bs-popper-config='{"strategy":"fixed"}'>
								<i class="fa-solid fa-ellipsis"></i>
							</button>
						  	<ul class="dropdown-menu dropdown-menu-end">
						        @permitted('admin/promotions/edit/' . $promotion->id)
						        <li>
						            <a class="dropdown-item" href="{{ URL::to('admin/promotions/edit/' . $promotion->id) }}">
						                <i class="fa-solid fa-pen-to-square"></i>{{ Lang::get('redminportal::buttons.edit') }}</a>
						        </li>
						        @endpermitted
						        @permitted('admin/promotions/delete/' . $promotion->id)
						        <li>
						            <a class="dropdown-item btn-confirm" href="{{ URL::to('admin/promotions/delete/' . $promotion->id) }}">
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
        <div class="p-3 rounded bg-body-secondary text-body-secondary">{{ Lang::get('redminportal::messages.no_promotion_found') }}</div>
        @endif
    @endif
@stop
