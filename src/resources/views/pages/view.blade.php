@extends('redminportal::layouts.master')

@section('navbar-breadcrumb')
    <li class="active"><span class="navbar-text">{{ Lang::get('redminportal::menus.pages') }}</span></li>
@stop

@section('content')

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
                    <th>{{ Lang::get('redminportal::forms.title') }}</th>
                    <th>{{ Lang::get('redminportal::forms.category') }}</th>
                    <th>{{ Lang::get('redminportal::forms.slug') }}</th>
                    <th>{{ Lang::get('redminportal::forms.created') }}</th>
                    <th>{{ Lang::get('redminportal::forms.updated') }}</th>
                    <th>{{ Lang::get('redminportal::forms.private') }}</th>
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
        {!! $pages->render() !!}
        </div>
    @else
        <div class="alert alert-info">{{ Lang::get('redminportal::messages.no_page_found') }}</div>
    @endif
@stop
