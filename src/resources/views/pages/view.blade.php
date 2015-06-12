@extends('redminportal::layouts.master')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <ol class="breadcrumb">
                <li><a href="{{ URL::to('admin') }}">{{ Lang::get('redminportal::menus.home') }}</a></li>
                <li class="active">{{ Lang::get('redminportal::menus.pages') }}</li>
            </ol>
        </div>
    </div>

    @include('redminportal::partials.errors')
    
    <div class="row">
        <div class="col-md-12">
            <div class="nav-controls text-right">
                <div class="btn-group" role="group">
                @if (count($pages) > 0)
                <a href="" class="btn btn-default btn-sm disabled btn-text">{{ $pages->firstItem() . ' to ' . $pages->lastItem() . ' of ' . $pages->total() }}</a>
                @endif
                {!! HTML::link('admin/pages/create', Lang::get('redminportal::buttons.create_new'), array('class' => 'btn btn-primary btn-sm')) !!}
            </div>
            </div>
        </div>
    </div>
    
    @if (count($pages) > 0)
        <table class='table table-striped table-bordered table-condensed'>
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Slug</th>
                    <th>Created</th>
                    <th>Updated</th>
                    <th>Private</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            @foreach ($pages as $page)
                <tr>
                    <td>{{ $page->title }}</td>
                    <td>@if($page->category_id){{ $page->category->name }}@else{{ 'No category' }}@endif</td>
                    <td>{{ $page->slug }}</td>
                    <td>{{ date('d-M-y', strtotime($page->created_at)) }}</td>
                    <td>{{ date('d-M-y', strtotime($page->updated_at)) }}</td>
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
                                        <i class="glyphicon glyphicon-edit"></i>Edit</a>
                                </li>
                                <li>
                                    <a href="{{ URL::to('admin/pages/delete/' . $page->id) }}" class="btn-confirm">
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
        {!! $pages->render() !!}
        </div>
    @else
        <div class="alert alert-info">No page found</div>
    @endif
@stop
