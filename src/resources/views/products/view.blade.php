@extends('redminportal::layouts.master')

@section('content')
    @if($errors->has())
    <div class='alert alert-danger'>
        We encountered the following errors:
        <ul>
            @foreach($errors->all() as $message)
            <li>{{ $message }}</li>
            @endforeach
        </ul>
    </div>
    @endif
    
    <div class="nav-controls text-right">
        @if (count($products) > 0)
        <span class="label label-default pull-left">
            {{ $products->firstItem() . ' to ' . $products->lastItem() . ' ( total ' . $products->total() . ' )' }}
        </span>
        @endif
        {{ HTML::link('admin/products/create', 'Create New', array('class' => 'btn btn-primary')) }}
    </div>

    @if (count($products) > 0)
        <table class='table table-striped table-bordered'>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Category</th>
                    <th>SKU</th>
                    <th>Price</th>
                    <th>Short Description</th>
                    <th class='hide'>Long Description</th>
                    <th>Tags</th>
                    <th>Featured</th>
                    <th>Active</th>
                    <th class='hide'>Photos</th>
                    <th class='hide'>Updated</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            @foreach ($products as $product)
                <tr>
                    <td>{{ $product->name }}</td>
                    <td>{{ $product->category->name }}</td>
                    <td>{{ $product->sku }}</td>
                    <td>{{ $product->price }}</td>
                    <td>{{ $product->short_description }}</td>
                    <td class='hide'>{{ $product->long_description }}</td>
                    <td>
                        @foreach( $product->tags as $tag)
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
                    <td class='hide'>
                        @foreach( $product->images as $image )
                        {{ $image->path }}<br/>
                        @endforeach
                    </td>
                    <td class='hide'>{{ $product->updated_at }}</td>
                    <td>
                        <div class="btn-group">
                            <button type="button" class="btn btn-default btn-xs dropdown-toggle" data-toggle="dropdown">
                                Action <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu pull-right" role="menu">
                                <li>
                                    <a href="{{ URL::to('admin/products/edit/' . $product->id) }}">
                                        <i class="glyphicon glyphicon-edit"></i>Edit</a>
                                </li>
                                <li>
                                    <a href="{{ URL::to('admin/products/delete/' . $product->id) }}" class="btn-confirm">
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
        {!! $products->render() !!}
        </div>
    @else
        <div class="alert alert-info">No product found</div>
    @endif
@stop
