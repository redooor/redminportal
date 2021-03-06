@extends('redminportal::layouts.master')

@section('navbar-breadcrumb')
    <li class="active"><span class="navbar-text">{{ Lang::get('redminportal::menus.pages') }}</span></li>
@stop

@section('content')

    @include('redminportal::partials.errors')
    
    @include('redminportal::partials.html-view-nav-controls', ['models' => $models, 'model_view' => 'pages'])
    
    @if (count($models) > 0)
        <table class='table table-striped table-bordered table-condensed'>
            <thead>
                <tr>
                    <th>{!! Redminportal::html()->sorter('admin/pages', 'title', $sortBy, $orderBy) !!}</th>
                    <th>{!! Redminportal::html()->sorter('admin/pages', 'category_name', $sortBy, $orderBy, trans('redminportal::forms.category')) !!}</th>
                    <th>{!! Redminportal::html()->sorter('admin/pages', 'slug', $sortBy, $orderBy) !!}</th>
                    <th>{!! Redminportal::html()->sorter('admin/pages', 'created_at', $sortBy, $orderBy) !!}</th>
                    <th>{!! Redminportal::html()->sorter('admin/pages', 'updated_at', $sortBy, $orderBy) !!}</th>
                    <th>{!! Redminportal::html()->sorter('admin/pages', 'private', $sortBy, $orderBy) !!}</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            @foreach ($models as $page)
                <tr>
                    <td>{{ $page->title }}</td>
                    <td>@if($page->category_id){{ $page->category->name }}@else{{ 'No category' }}@endif</td>
                    <td>{{ $page->slug }}</td>
                    <td>{{ date('d/m/y h:i A', strtotime($page->created_at)) }}</td>
                    <td>{{ date('d/m/y h:i A', strtotime($page->updated_at)) }}</td>
                    <td>
                        @if ($page->private)
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
                                    <a href="{{ URL::to('admin/pages/edit/' . $page->id) }}">
                                        <i class="glyphicon glyphicon-edit"></i>{{ Lang::get('redminportal::buttons.edit') }}</a>
                                </li>
                                <li>
                                    <a href="{{ URL::to('admin/pages/delete/' . $page->id) }}" class="btn-confirm">
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
        @if ($models->lastPage())
        <div class="alert alert-info">{{ Lang::get('redminportal::messages.no_record_page_empty') }}</div>
        <a href="{{ $models->url($models->lastPage()) }}" class="btn btn-default"><span class="glyphicon glyphicon-menu-left"></span> {{ Lang::get('redminportal::buttons.previous_page') }}</a>
        @else
        <div class="alert alert-info">{{ Lang::get('redminportal::messages.no_page_found') }}</div>
        @endif
    @endif
@stop
