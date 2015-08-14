@extends('redminportal::layouts.master')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <ol class="breadcrumb">
                <li><a href="{{ URL::to('admin') }}">{{ Lang::get('redminportal::menus.home') }}</a></li>
                <li class="active">{{ Lang::get('redminportal::menus.bundles') }}</li>
            </ol>
        </div>
    </div>

    @include('redminportal::partials.errors')
    
    <div class="row">
        <div class="col-md-12">
            <div class="nav-controls text-right">
                <div class="btn-group" role="group">
                @if (count($bundles) > 0)
                <a href="" class="btn btn-default btn-sm disabled btn-text">{{ $bundles->firstItem() . ' to ' . $bundles->lastItem() . ' of ' . $bundles->total() }}</a>
                @endif
                {!! HTML::link('admin/bundles/create', Lang::get('redminportal::buttons.create_new'), array('class' => 'btn btn-primary btn-sm')) !!}
            </div>
            </div>
        </div>
    </div>

    @if (count($bundles) > 0)
        <table class='table table-striped table-bordered table-condensed'>
            <thead>
                <tr>
                    <th>
                        <a href="{{ URL::to('admin/bundles/sort') . '/name/' . ($sortBy == 'name' && $orderBy == 'asc' ? 'desc' : 'asc') }}">
                            {{ Lang::get('redminportal::forms.name') }}
                            @if ($sortBy == 'name')
                            {!! ($orderBy == 'asc' ? '<span class="caret"></span>' : '<span class="dropup"><span class="caret"></span></span>') !!}
                            @endif
                        </a>
                    </th>
                    <th>{{ Lang::get('redminportal::forms.category') }}</th>
                    <th>
                        <a href="{{ URL::to('admin/bundles/sort') . '/sku/' . ($sortBy == 'sku' && $orderBy == 'asc' ? 'desc' : 'asc') }}">
                            {{ Lang::get('redminportal::forms.sku') }}
                            @if ($sortBy == 'sku')
                            {!! ($orderBy == 'asc' ? '<span class="caret"></span>' : '<span class="dropup"><span class="caret"></span></span>') !!}
                            @endif
                        </a>
                    </th>
                    <th>{{ Lang::get('redminportal::forms.price') }}</th>
                    <th>{{ Lang::get('redminportal::forms.total_value') }}</th>
                    <th>{{ Lang::get('redminportal::forms.summary') }}</th>
                    <th>{{ Lang::get('redminportal::forms.tags') }}</th>
                    <th>
                        <a href="{{ URL::to('admin/bundles/sort') . '/featured/' . ($sortBy == 'featured' && $orderBy == 'asc' ? 'desc' : 'asc') }}">
                            {{ Lang::get('redminportal::forms.featured') }}
                            @if ($sortBy == 'featured')
                            {!! ($orderBy == 'asc' ? '<span class="caret"></span>' : '<span class="dropup"><span class="caret"></span></span>') !!}
                            @endif
                        </a>
                    </th>
                    <th>
                        <a href="{{ URL::to('admin/bundles/sort') . '/active/' . ($sortBy == 'active' && $orderBy == 'asc' ? 'desc' : 'asc') }}">
                            {{ Lang::get('redminportal::forms.active') }}
                            @if ($sortBy == 'active')
                            {!! ($orderBy == 'asc' ? '<span class="caret"></span>' : '<span class="dropup"><span class="caret"></span></span>') !!}
                            @endif
                        </a>
                    </th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            @foreach ($bundles as $bundle)
                <tr>
                    <td>{{ $bundle->name }}</td>
                    <td>{{ $bundle->category->name or 'No category' }}</td>
                    <td>{{ $bundle->sku }}</td>
                    <td>{{ number_format($bundle->price) }}</td>
                    <td>{{ number_format($bundle->totalvalue()) }}</td>
                    <td>{{ $bundle->short_description }}</td>
                    <td>
                        @foreach ($bundle->tags as $tag)
                        <span class="label label-info">{{ $tag->name }}</span>
                        @endforeach
                    </td>
                    <td>
                        @if ($bundle->featured)
                            <span class="label label-success"><span class='glyphicon glyphicon-ok'></span></span>
                        @else
                            <span class="label label-danger"><span class='glyphicon glyphicon-remove'></span></span>
                        @endif
                    </td>
                    <td>
                        @if ($bundle->active)
                            <span class="label label-success"><span class='glyphicon glyphicon-ok'></span></span>
                        @else
                            <span class="label label-danger"><span class='glyphicon glyphicon-remove'></span></span>
                        @endif
                    </td>
                    <td class="table-actions text-right">
                        <div class="btn-group">
                            <button type="button" class="btn btn-link dropdown-toggle" data-toggle="dropdown">
								<span class="glyphicon glyphicon-option-horizontal"></span>
							</button>
                            <ul class="dropdown-menu pull-right" role="menu">
                                <li>
                                    <a href="{{ URL::to('admin/bundles/edit/' . $bundle->id) }}">
                                        <i class="glyphicon glyphicon-edit"></i>{{ Lang::get('redminportal::buttons.edit') }}</a>
                                </li>
                                <li>
                                    <a href="{{ URL::to('admin/bundles/delete/' . $bundle->id) }}" class="btn-confirm">
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
        {!! $bundles->render() !!}
        </div>
    @else
        <div class="alert alert-info">{{ Lang::get('redminportal::messages.no_bundle_found') }}</div>
    @endif
@stop
