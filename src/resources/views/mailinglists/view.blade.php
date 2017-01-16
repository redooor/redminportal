@extends('redminportal::layouts.master')

@section('navbar-breadcrumb')
    <li class="active"><span class="navbar-text">{{ Lang::get('redminportal::menus.mailinglist') }}</span></li>
@stop

@section('content')

    @include('redminportal::partials.errors')
    
    <div class="row">
        <div class="col-md-12">
            <div class="nav-controls text-right">
                <div class="btn-group" role="group">
                @if (count($models) > 0)
                    <a href="" class="btn btn-default btn-sm disabled btn-text">{{ trans('redminportal::messages.list_from_to', ['firstItem' => $models->firstItem(), 'lastItem' => $models->lastItem(), 'totalItem' => $models->total()]) }}</a>
                @endif
                <button class="btn btn-default btn-sm" data-toggle="modal" data-target="#export-csv">{{ Lang::get('redminportal::buttons.export_csv') }}</button>
                <a href="{{ url('admin/mailinglists/create') }}" class="btn btn-primary btn-sm">{{ Lang::get('redminportal::buttons.create_new') }}</a>
            </div>
            </div>
        </div>
    </div>

    @if (count($models) > 0)
        <table class='table table-striped table-bordered table-condensed'>
            <thead>
                <tr>
                    <th>
                        {!! Redminportal::html()->sorter('admin/mailinglists', 'email', $sortBy, $orderBy) !!}
                    </th>
                    <th>
                        {!! Redminportal::html()->sorter('admin/mailinglists', 'first_name', $sortBy, $orderBy) !!}
                    </th>
                    <th>
                        {!! Redminportal::html()->sorter('admin/mailinglists', 'last_name', $sortBy, $orderBy) !!}
                    </th>
                    <th>
                        {!! Redminportal::html()->sorter('admin/mailinglists', 'active', $sortBy, $orderBy) !!}
                    </th>
                    <th>
                        {!! Redminportal::html()->sorter('admin/mailinglists', 'created_at', $sortBy, $orderBy) !!}
                    </th>
                    <th>
                        {!! Redminportal::html()->sorter('admin/mailinglists', 'updated_at', $sortBy, $orderBy) !!}
                    </th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            @foreach ($models as $mailinglist)
                <tr>
                    <td>{{ $mailinglist->email }}</td>
                    <td>{{ $mailinglist->first_name }}</td>
                    <td>{{ $mailinglist->last_name }}</td>
                    <td>
                        @if ($mailinglist->active)
                            <span class="label label-success"><span class='glyphicon glyphicon-ok'></span></span>
                        @else
                            <span class="label label-danger"><span class='glyphicon glyphicon-remove'></span></span>
                        @endif
                    </td>
                    <td>{{ date("d/m/y h:i A", strtotime($mailinglist->created_at)) }}</td>
                    <td>{{ date("d/m/y h:i A", strtotime($mailinglist->updated_at)) }}</td>
                    <td class="table-actions text-right">
                        <div class="btn-group">
                            <button type="button" class="btn btn-link dropdown-toggle" data-toggle="dropdown">
								<span class="glyphicon glyphicon-option-horizontal"></span>
							</button>
                            <ul class="dropdown-menu pull-right" role="menu">
                                <li>
                                    <a href="{{ URL::to('admin/mailinglists/edit/' . $mailinglist->id) }}">
                                        <i class="glyphicon glyphicon-edit"></i>{{ Lang::get('redminportal::buttons.edit') }}</a>
                                </li>
                                <li>
                                    <a href="{{ URL::to('admin/mailinglists/delete/' . $mailinglist->id) }}" class="btn-confirm">
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
        <div class="alert alert-info">{{ Lang::get('redminportal::messages.no_mailinglist_found') }}</div>
        @endif
    @endif
    {{-- Include Export Modal --}}
    @include('redminportal::partials.modal-export', [
        'export_id' => 'export-csv',
        'export_title' => trans('redminportal::messages.export_to_csv'),
        'export_url' => url('admin/reports/mailinglist')
    ])
@stop

@section('footer')
    <script>
        !function ($) {
            $(function(){
                // For datepicker
                $( '.datepicker' ).datepicker({ dateFormat: 'dd/mm/yy' });
                $('.datepicker').css('z-index', '1051'); // make picker on top of modal window
                $( '.input-group-addon' ).click( function() {
                    $( this ).parent().find('input').datepicker( "show" );
                });
            })
        }(window.jQuery);
    </script>
@stop
