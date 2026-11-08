@extends('redminportal::layouts.master')

@section('navbar-breadcrumb')
    <li class="nav-item"><span class="nav-link active">{{ Lang::get('redminportal::menus.bundles') }}</span></li>
@stop

@section('content')

    @include('redminportal::partials.errors')
    
    @include('redminportal::partials.html-view-nav-controls', ['models' => $models, 'model_view' => 'bundles'])

    @if (count($models) > 0)
        <table class='table table-striped table-sm'>
            <thead>
                <tr>
                    <th>{!! Redminportal::html()->sorter('admin/bundles', 'name', $sortBy, $orderBy) !!}</th>
                    <th>{!! Redminportal::html()->sorter('admin/bundles', 'category_name', $sortBy, $orderBy, trans('redminportal::forms.category')) !!}</th>
                    <th>{!! Redminportal::html()->sorter('admin/bundles', 'sku', $sortBy, $orderBy) !!}</th>
                    <th>{!! Redminportal::html()->sorter('admin/bundles', 'price', $sortBy, $orderBy) !!}</th>
                    <th>{{ Lang::get('redminportal::forms.total_value') }}</th>
                    <th>{{ Lang::get('redminportal::forms.summary') }}</th>
                    <th>{{ Lang::get('redminportal::forms.tags') }}</th>
                    <th>{!! Redminportal::html()->sorter('admin/bundles', 'featured', $sortBy, $orderBy) !!}</th>
                    <th>{!! Redminportal::html()->sorter('admin/bundles', 'active', $sortBy, $orderBy) !!}</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            @foreach ($models as $bundle)
                <tr>
                    <td>{{ $bundle->name }}</td>
                    <td>{{ $bundle->category->name ?? 'No category' }}</td>
                    <td>{{ $bundle->sku }}</td>
                    <td>{{ number_format($bundle->price, 2) }}</td>
                    <td>{{ number_format($bundle->totalvalue(), 2) }}</td>
                    <td>{{ $bundle->short_description }}</td>
                    <td>
                        @foreach ($bundle->tags as $tag)
                        <span class="badge text-bg-info">{{ $tag->name }}</span>
                        @endforeach
                    </td>
                    <td>
                        @if ($bundle->featured)
                            <span class="badge text-bg-success"><span class='fa-solid fa-check'></span></span>
                        @else
                            <span class="badge text-bg-danger"><span class='fa-solid fa-xmark'></span></span>
                        @endif
                    </td>
                    <td>
                        @if ($bundle->active)
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
                                    <a class="dropdown-item" href="{{ URL::to('admin/bundles/edit/' . $bundle->id) }}">
                                        <i class="fa-solid fa-pen-to-square"></i>{{ Lang::get('redminportal::buttons.edit') }}</a>
                                </li>
                                <li>
                                    <a class="dropdown-item btn-confirm" href="{{ URL::to('admin/bundles/delete/' . $bundle->id) }}">
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
        <div class="p-3 rounded bg-body-secondary text-body-secondary">{{ Lang::get('redminportal::messages.no_bundle_found') }}</div>
        @endif
    @endif
@stop
