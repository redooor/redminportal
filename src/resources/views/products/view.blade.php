@extends('redminportal::layouts.master')

@section('navbar-breadcrumb')
    <li class="active"><span class="navbar-text">{{ Lang::get('redminportal::menus.products') }}</span></li>
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
                {!! HTML::link('admin/products/create', Lang::get('redminportal::buttons.create_new'), array('class' => 'btn btn-primary btn-sm')) !!}
            </div>
            </div>
        </div>
    </div>

    @if (count($models) > 0)
        <table class='table table-striped table-bordered table-condensed'>
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
                        <span class="label label-info">{{ $tag->name }}</span>
                        @endforeach
                    </td>
                    <td>
                        @if ($product->featured)
                            <span class="label label-success"><span class='glyphicon glyphicon-ok'></span></span>
                        @else
                            <span class="label label-danger"><span class='glyphicon glyphicon-remove'></span></span>
                        @endif
                    </td>
                    <td>
                        @if ($product->active)
                            <span class="label label-success"><span class='glyphicon glyphicon-ok'></span></span>
                        @else
                            <span class="label label-danger"><span class='glyphicon glyphicon-remove'></span></span>
                        @endif
                    </td>
                    <td class="table-actions text-center">
                        @if (count($product->variants) > 0)
                        <div class="btn-group">
                            <button type="button" class="btn btn-link dropdown-toggle" data-toggle="dropdown">
								<span class="fa fa-sitemap"></span>
							</button>
							<ul class="dropdown-menu pull-right" role="menu">
                                @foreach ($product->variants as $variant)
								<li>
									<a href="#">{{ $variant->name }}<br><span class="label label-primary">{{ $variant->sku }}</span></a>
								</li>
                                @endforeach
							</ul>
						</div>
                        @endif
					</td>
                    <td class="table-actions text-right">
                        <div class="btn-group">
                            <button type="button" class="btn btn-link dropdown-toggle" data-toggle="dropdown">
								<span class="glyphicon glyphicon-option-horizontal"></span>
							</button>
                            <ul class="dropdown-menu pull-right" role="menu">
                                <li>
                                    <a href="{{ URL::to('admin/products/edit/' . $product->id) }}">
                                        <i class="glyphicon glyphicon-edit"></i>{{ Lang::get('redminportal::buttons.edit') }}</a>
                                </li>
                                <li>
                                    <a href="{{ URL::to('admin/products/delete/' . $product->id) }}" class="btn-confirm">
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
        <div class="alert alert-info">{{ Lang::get('redminportal::messages.no_product_found') }}</div>
    @endif
@stop
