@extends('redminportal::layouts.master')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <ol class="breadcrumb">
                <li><a href="{{ URL::to('admin') }}">{{ Lang::get('redminportal::menus.home') }}</a></li>
                <li class="active">{{ Lang::get('redminportal::menus.posts') }}</li>
            </ol>
        </div>
    </div>

    @include('redminportal::partials.errors')
    
    <div class="row">
        <div class="col-md-12">
            <div class="nav-controls text-right">
                <div class="btn-group" role="group">
                @if (count($posts) > 0)
                <a href="" class="btn btn-default btn-sm disabled btn-text">{{ $posts->firstItem() . ' to ' . $posts->lastItem() . ' of ' . $posts->total() }}</a>
                @endif
                {!! HTML::link('admin/posts/create', Lang::get('redminportal::buttons.create_new'), array('class' => 'btn btn-primary btn-sm')) !!}
            </div>
            </div>
        </div>
    </div>
    
    @if (count($posts) > 0)
        <table class='table table-striped table-bordered table-condensed'>
            <thead>
                <tr>
                    <th>{{ Lang::get('redminportal::forms.title') }}</th>
                    <th>{{ Lang::get('redminportal::forms.category') }}</th>
                    <th>{{ Lang::get('redminportal::forms.slug') }}</th>
                    <th>{{ Lang::get('redminportal::forms.created') }}</th>
                    <th>{{ Lang::get('redminportal::forms.updated') }}</th>
                    <th>{{ Lang::get('redminportal::forms.featured') }}</th>
                    <th>{{ Lang::get('redminportal::forms.private') }}</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            @foreach ($posts as $post)
                <tr>
                    <td>{{ $post->title }}</td>
                    <td>@if($post->category_id){{ $post->category->name }}@else{{ 'No category' }}@endif</td>
                    <td>{{ $post->slug }}</td>
                    <td>{{ date('d-M-y', strtotime($post->created_at)) }}</td>
                    <td>{{ date('d-M-y', strtotime($post->updated_at)) }}</td>
                    <td>
                        @if ($post->featured)
                            <span class="label label-success"><span class='glyphicon glyphicon-ok'></span></span>
                        @else
                            <span class="label label-danger"><span class='glyphicon glyphicon-remove'></span></span>
                        @endif
                    </td>
                    <td>
                        @if ($post->private)
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
                                    <a href="{{ URL::to('admin/posts/edit/' . $post->id) }}">
                                        <i class="glyphicon glyphicon-edit"></i>{{ Lang::get('redminportal::buttons.edit') }}</a>
                                </li>
                                <li>
                                    <a href="{{ URL::to('admin/posts/delete/' . $post->id) }}" class="btn-confirm">
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
        {!! $posts->render() !!}
        </div>
    @else
        <div class="alert alert-info">{{ Lang::get('redminportal::messages.no_post_found') }}</div>
    @endif
@stop
