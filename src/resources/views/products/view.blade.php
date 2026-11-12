@extends('redminportal::layouts.master')

@section('navbar-breadcrumb')
    <li class="nav-item"><span class="nav-link active">{{ Lang::get('redminportal::menus.products') }}</span></li>
@stop

@section('content')
    @include('redminportal::partials.confirm-delete')

    @include('redminportal::partials.errors')
    
    @include('redminportal::partials.html-view-nav-controls', ['models' => $models, 'model_view' => 'products'])

    @if (count($models) > 0)
        <div class="table-responsive">
        <table class='table table-striped table-sm'>
            <thead>
                <tr>
                    <th>{!! Redminportal::html()->sorter('admin/products', 'name', $sortBy, $orderBy) !!}</th>
                    <th>{!! Redminportal::html()->sorter('admin/products', 'category_name', $sortBy, $orderBy, trans('redminportal::forms.category')) !!}</th>
                    <th>{!! Redminportal::html()->sorter('admin/products', 'sku', $sortBy, $orderBy) !!}</th>
                    <th>{!! Redminportal::html()->sorter('admin/products', 'price', $sortBy, $orderBy) !!}</th>
                    <th>{{ Lang::get('redminportal::forms.summary') }}</th>
                    <th>{{ Lang::get('redminportal::forms.tags') }}</th>
                    <th>{!! Redminportal::html()->sorter('admin/products', 'featured', $sortBy, $orderBy) !!}</th>
                    <th>{!! Redminportal::html()->sorter('admin/products', 'active', $sortBy, $orderBy) !!}</th>
                    <th>{{ Lang::get('redminportal::forms.variation') }}</th>
                    
                    <th></th>
                </tr>
            </thead>
            <tbody>
            @foreach ($models as $product)
                <tr>
                    <td>{{ $product->name }}</td>
                    <td>{{ $product->category->name }}</td>
                    <td>{{ $product->sku }}</td>
                    <td>{{ $product->price }}</td>
                    <td>{{ $product->short_description }}</td>
                    <td>
                        @foreach ($product->tags as $tag)
                        <span class="badge text-bg-info">{{ $tag->name }}</span>
                        @endforeach
                    </td>
                    <td>
                        @if ($product->featured)
                            <span class="badge text-bg-success"><span class='fa-solid fa-check'></span></span>
                        @else
                            <span class="badge text-bg-danger"><span class='fa-solid fa-xmark'></span></span>
                        @endif
                    </td>
                    <td>
                        @if ($product->active)
                            <span class="badge text-bg-success"><span class='fa-solid fa-check'></span></span>
                        @else
                            <span class="badge text-bg-danger"><span class='fa-solid fa-xmark'></span></span>
                        @endif
                    </td>
                    <td class="table-actions text-center">
                        @if (count($product->variants) > 0)
                        <div class="btn-group">
                            <button type="button" class="btn btn-link dropdown-toggle" data-bs-toggle="dropdown" data-bs-popper-config='{"strategy":"fixed"}'>
								<i class="fa-solid fa-sitemap"></i>
							</button>
							<ul class="dropdown-menu dropdown-menu-end">
                                @foreach ($product->variants as $variant)
								<li>
									<a class="dropdown-item" href="#">{{ $variant->name }}<br><span class="badge text-bg-primary">{{ $variant->sku }}</span></a>
								</li>
                                @endforeach
							</ul>
						</div>
                        @endif
					</td>
                    <td class="table-actions text-end">
                        <div class="btn-group">
                            <button type="button" class="btn btn-link dropdown-toggle" data-bs-toggle="dropdown" data-bs-popper-config='{"strategy":"fixed"}'>
								<i class="fa-solid fa-ellipsis"></i>
							</button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item" href="{{ URL::to('admin/products/edit/' . $product->id) }}">
                                        <i class="fa-solid fa-pen-to-square"></i>{{ Lang::get('redminportal::buttons.edit') }}</a>
                                </li>
                                <li>
                                    <a class="dropdown-item btn-confirm" href="{{ URL::to('admin/products/delete/' . $product->id) }}">
                                        <i class="fa-solid fa-xmark"></i>{{ Lang::get('redminportal::buttons.delete') }}</a>
                                </li>
                            </ul>
                        </div>
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
        <div class="p-3 rounded bg-body-secondary text-body-secondary">{{ Lang::get('redminportal::messages.no_product_found') }}</div>
        @endif
    @endif
@stop
