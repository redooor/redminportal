@extends('redminportal::layouts.master')

@section('navbar-breadcrumb')
    <li class="nav-item"><span class="nav-link active">{{ Lang::get('redminportal::menus.portfolios') }}</span></li>
@stop

@section('content')
    @include('redminportal::partials.confirm-delete')

    @include('redminportal::partials.errors')
    
    @include('redminportal::partials.html-view-nav-controls', ['models' => $models, 'model_view' => 'portfolios'])
    
    @if (count($models) > 0)
        <div class="table-responsive">
        <table class='table table-striped table-sm'>
            <thead>
                <tr>
                    <th>{!! Redminportal::html()->sorter('admin/portfolios', 'name', $sortBy, $orderBy) !!}</th>
                    <th>{!! Redminportal::html()->sorter('admin/portfolios', 'category_name', $sortBy, $orderBy, trans('redminportal::forms.category')) !!}</th>
                    <th>{!! Redminportal::html()->sorter('admin/portfolios', 'short_description', $sortBy, $orderBy, trans('redminportal::forms.summary')) !!}</th>
                    <th>{!! Redminportal::html()->sorter('admin/portfolios', 'active', $sortBy, $orderBy) !!}</th>
                    <th>{!! Redminportal::html()->sorter('admin/portfolios', 'created_at', $sortBy, $orderBy) !!}</th>
                    <th>{!! Redminportal::html()->sorter('admin/portfolios', 'updated_at', $sortBy, $orderBy) !!}</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            @foreach ($models as $portfolio)
                <tr>
                    <td>{{ $portfolio->name }}</td>
                    <td>{{ $portfolio->category->name }}</td>
                    <td>{{ $portfolio->short_description }}</td>
                    <td>
                        @if ($portfolio->active)
                            <span class="badge text-bg-success"><span class='fa-solid fa-check'></span></span>
                        @else
                            <span class="badge text-bg-danger"><span class='fa-solid fa-xmark'></span></span>
                        @endif
                    </td>
                    <td>{{ date('d/m/y h:i A', strtotime($portfolio->created_at)) }}</td>
                    <td>{{ date('d/m/y h:i A', strtotime($portfolio->updated_at)) }}</td>
                    <td class="table-actions text-end">
                        @permitted('admin/portfolios/edit/' . $portfolio->id, 'admin/portfolios/delete/' . $portfolio->id)
                        <div class="btn-group">
                            <button type="button" class="btn btn-link dropdown-toggle" data-bs-toggle="dropdown" data-bs-popper-config='{"strategy":"fixed"}'>
								<i class="fa-solid fa-ellipsis"></i>
							</button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                @permitted('admin/portfolios/edit/' . $portfolio->id)
                                <li>
                                    <a class="dropdown-item" href="{{ URL::to('admin/portfolios/edit/' . $portfolio->id) }}">
                                        <i class="fa-solid fa-pen-to-square"></i>{{ Lang::get('redminportal::buttons.edit') }}</a>
                                </li>
                                @endpermitted
                                @permitted('admin/portfolios/delete/' . $portfolio->id)
                                <li>
                                    <a class="dropdown-item btn-confirm" href="{{ URL::to('admin/portfolios/delete/' . $portfolio->id) }}">
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
        <div class="p-3 rounded bg-body-secondary text-body-secondary">{{ Lang::get('redminportal::messages.no_portfolio_found') }}</div>
        @endif
    @endif
@stop
