@extends('redminportal::layouts.master')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <ol class="breadcrumb">
                <li><a href="{{ URL::to('admin') }}">{{ Lang::get('redminportal::menus.home') }}</a></li>
                <li class="active">{{ Lang::get('redminportal::menus.mailinglist') }}</li>
            </ol>
        </div>
    </div>

    @include('redminportal::partials.errors')
    
    <div class="row">
        <div class="col-md-12">
            <div class="nav-controls text-right">
                <div class="btn-group" role="group">
                @if (count($mailinglists) > 0)
                <a href="" class="btn btn-default btn-sm disabled btn-text">{{ $mailinglists->firstItem() . ' to ' . $mailinglists->lastItem() . ' of ' . $mailinglists->total() }}</a>
                @endif
                <button class="btn btn-default btn-sm" data-toggle="modal" data-target="#export-csv">{{ Lang::get('redminportal::buttons.export_csv') }}</button>
                {!! HTML::link('admin/mailinglists/create', Lang::get('redminportal::buttons.create_new'), array('class' => 'btn btn-primary btn-sm')) !!}
            </div>
            </div>
        </div>
    </div>

    @if (count($mailinglists) > 0)
        <table class='table table-striped table-bordered table-condensed'>
            <thead>
                <tr>
                    <th>
                        <a href="{{ URL::to('admin/mailinglists/sort') . '/email/' . ($sortBy == 'email' && $orderBy == 'asc' ? 'desc' : 'asc') }}">
                            {{ Lang::get('redminportal::forms.email') }}
                            @if ($sortBy == 'email')
                            {!! ($orderBy == 'asc' ? '<span class="caret"></span>' : '<span class="dropup"><span class="caret"></span></span>') !!}
                            @endif
                        </a>
                    </th>
                    <th>
                        <a href="{{ URL::to('admin/mailinglists/sort') . '/first_name/' . ($sortBy == 'first_name' && $orderBy == 'asc' ? 'desc' : 'asc') }}">
                            {{ Lang::get('redminportal::forms.first_name') }}
                            @if ($sortBy == 'first_name')
                            {!! ($orderBy == 'asc' ? '<span class="caret"></span>' : '<span class="dropup"><span class="caret"></span></span>') !!}
                            @endif
                        </a>
                    </th>
                    <th>
                        <a href="{{ URL::to('admin/mailinglists/sort') . '/last_name/' . ($sortBy == 'last_name' && $orderBy == 'asc' ? 'desc' : 'asc') }}">
                            {{ Lang::get('redminportal::forms.last_name') }}
                            @if ($sortBy == 'last_name')
                            {!! ($orderBy == 'asc' ? '<span class="caret"></span>' : '<span class="dropup"><span class="caret"></span></span>') !!}
                            @endif
                        </a>
                    </th>
                    <th>
                        <a href="{{ URL::to('admin/mailinglists/sort') . '/active/' . ($sortBy == 'active' && $orderBy == 'asc' ? 'desc' : 'asc') }}">
                            {{ Lang::get('redminportal::forms.active') }}
                            @if ($sortBy == 'active')
                            {!! ($orderBy == 'asc' ? '<span class="caret"></span>' : '<span class="dropup"><span class="caret"></span></span>') !!}
                            @endif
                        </a>
                    </th>
                    <th>
                        <a href="{{ URL::to('admin/mailinglists/sort') . '/updated_at/' . ($sortBy == 'updated_at' && $orderBy == 'asc' ? 'desc' : 'asc') }}">
                            {{ Lang::get('redminportal::forms.updated') }}
                            @if ($sortBy == 'updated_at')
                            {!! ($orderBy == 'asc' ? '<span class="caret"></span>' : '<span class="dropup"><span class="caret"></span></span>') !!}
                            @endif
                        </a>
                    </th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            @foreach ($mailinglists as $mailinglist)
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
                    <td>{{ date('d-M-y', strtotime($mailinglist->updated_at)) }}</td>
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
        {!! $mailinglists->render() !!}
        </div>
    @else
        <div class="alert alert-info">{{ Lang::get('redminportal::messages.no_mailinglist_found') }}</div>
    @endif
    <div id="export-csv" class="modal fade">
        <div class="modal-dialog">
            <div class="modal-content">
                {!! Form::open(array('action' => '\Redooor\Redminportal\App\Http\Controllers\ReportController@postMailinglist', 'report' => 'form')) !!}
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">{{ Lang::get('redminportal::buttons.close') }}</span></button>
                    <h4 class="modal-title">{{ Lang::get('redminportal::messages.export_to_csv') }}</h4>
                </div>
                <div class="modal-body">
                    <div class='row'>
                        <div class="col-md-12">
                            <div class="form-group">
                                {!! Form::label('start_date', Lang::get('redminportal::forms.start_date')) !!}
                                <div class="input-group" id='start-date'>
                                    {!! Form::input('text', 'start_date', null, array('class' => 'form-control datepicker', 'readonly')) !!}
                                    <span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>
                                </div>
                            </div>
                            <div class="form-group">
                                {!! Form::label('end_date', Lang::get('redminportal::forms.end_date')) !!}
                                <div class="input-group" id='end-date'>
                                    {!! Form::input('text', 'end_date', null, array('class' => 'form-control datepicker', 'readonly')) !!}
                                    <span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>
                                </div>
                            </div>
                            <p class="help-block">{{ Lang::get('redminportal::messages.leave_all_blank_to_download_all') }}</p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">{{ Lang::get('redminportal::buttons.close') }}</button>
                    {!! Form::submit(Lang::get('redminportal::buttons.download_csv'), array('class' => 'btn btn-primary')) !!}
                </div>
                {!! Form::close() !!}
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->
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
