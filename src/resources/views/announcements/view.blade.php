@extends('redminportal::layouts.master')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <ol class="breadcrumb">
                <li><a href="{{ URL::to('admin') }}">{{ Lang::get('redminportal::menus.home') }}</a></li>
                <li class="active">{{ Lang::get('redminportal::menus.announcements') }}</li>
            </ol>
        </div>
    </div>

    @include('redminportal::partials.errors')
    
    <div class="row">
        <div class="col-md-12">
            <div class="nav-controls text-right">
                <div class="btn-group" role="group">
                @if (count($announcements) >0)
                <a href="" class="btn btn-default btn-sm disabled btn-text">{{ $announcements->firstItem() . ' to ' . $announcements->lastItem() . ' of ' . $announcements->total() }}</a>
                @endif
                {!! HTML::link('admin/announcements/create', Lang::get('redminportal::buttons.create_new'), array('class' => 'btn btn-primary btn-sm')) !!}
            </div>
            </div>
        </div>
    </div>

    @if (count($announcements) >0)
        <table class='table table-striped table-bordered table-condensed'>
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Created</th>
                    <th>Updated</th>
                    <th>Private</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            @foreach ($announcements as $announcement)
                <tr>
                    <td>{{ $announcement->title }}</td>
                    <td>{{ date('d-M-y', strtotime($announcement->created_at)) }}</td>
                    <td>{{ date('d-M-y', strtotime($announcement->updated_at)) }}</td>
                    <td>
                        @if ($announcement->private)
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
						            <a href="{{ URL::to('admin/announcements/edit/' . $announcement->id) }}">
						                <i class="glyphicon glyphicon-edit"></i>Edit</a>
						        </li>
						        <li>
						            <a href="{{ URL::to('admin/announcements/delete/' . $announcement->id) }}" class="btn-confirm">
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
        {!! $announcements->render() !!}
        </div>
    @else
        <div class="alert alert-info">No announcement found</div>
    @endif
@stop
