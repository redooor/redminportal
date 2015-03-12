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
        @if (count($mailinglists) > 0)
        <span class="label label-default pull-left">
            {{ $mailinglists->getFrom() . ' to ' . $mailinglists->getTo() . ' ( total ' . $mailinglists->getTotal() . ' )' }}
        </span>
        @endif
        <button class="btn btn-default" data-toggle="modal" data-target="#export-csv">Export CSV</button>
        {{ HTML::link('admin/mailinglists/create', 'Create New', array('class' => 'btn btn-primary')) }}
    </div>

    @if (count($mailinglists) > 0)
        <table class='table table-striped table-bordered'>
            <thead>
                <tr>
                    <th>
                        <a href="{{ URL::to('admin/mailinglists/sort') . '/email/' . ($sortBy == 'email' && $orderBy == 'asc' ? 'desc' : 'asc') }}">
                            Email
                            @if ($sortBy == 'email')
                            {{ ($orderBy == 'asc' ? '<span class="caret"></span>' : '<span class="dropup"><span class="caret"></span></span>') }}
                            @endif
                        </a>
                    </th>
                    <th>
                        <a href="{{ URL::to('admin/mailinglists/sort') . '/first_name/' . ($sortBy == 'first_name' && $orderBy == 'asc' ? 'desc' : 'asc') }}">
                            First Name
                            @if ($sortBy == 'first_name')
                            {{ ($orderBy == 'asc' ? '<span class="caret"></span>' : '<span class="dropup"><span class="caret"></span></span>') }}
                            @endif
                        </a>
                    </th>
                    <th>
                        <a href="{{ URL::to('admin/mailinglists/sort') . '/last_name/' . ($sortBy == 'last_name' && $orderBy == 'asc' ? 'desc' : 'asc') }}">
                            Last Name
                            @if ($sortBy == 'last_name')
                            {{ ($orderBy == 'asc' ? '<span class="caret"></span>' : '<span class="dropup"><span class="caret"></span></span>') }}
                            @endif
                        </a>
                    </th>
                    <th>
                        <a href="{{ URL::to('admin/mailinglists/sort') . '/active/' . ($sortBy == 'active' && $orderBy == 'asc' ? 'desc' : 'asc') }}">
                            Active
                            @if ($sortBy == 'active')
                            {{ ($orderBy == 'asc' ? '<span class="caret"></span>' : '<span class="dropup"><span class="caret"></span></span>') }}
                            @endif
                        </a>
                    </th>
                    <th>
                        <a href="{{ URL::to('admin/mailinglists/sort') . '/updated_at/' . ($sortBy == 'updated_at' && $orderBy == 'asc' ? 'desc' : 'asc') }}">
                            Updated
                            @if ($sortBy == 'updated_at')
                            {{ ($orderBy == 'asc' ? '<span class="caret"></span>' : '<span class="dropup"><span class="caret"></span></span>') }}
                            @endif
                        </a>
                    </th>
                    <th>Action</th>
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
                    <td>{{ $mailinglist->updated_at }}</td>
                    <td>
                        <div class="btn-group">
                            <button type="button" class="btn btn-default btn-xs dropdown-toggle" data-toggle="dropdown">
                                Action <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu pull-right" role="menu">
                                <li>
                                    <a href="{{ URL::to('admin/mailinglists/edit/' . $mailinglist->id) }}">
                                        <i class="glyphicon glyphicon-edit"></i>Edit</a>
                                </li>
                                <li>
                                    <a href="{{ URL::to('admin/mailinglists/delete/' . $mailinglist->id) }}" class="btn-confirm">
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
        {{ $mailinglists->links() }}
        </div>
    @else
        <div class="alert alert-info">No mailing list found</div>
    @endif
    <div id="export-csv" class="modal fade">
        <div class="modal-dialog">
            <div class="modal-content">
                {{ Form::open(array('action' => 'Redooor\Redminportal\ReportController@postMailinglist', 'report' => 'form')) }}
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                    <h4 class="modal-title">Export to CSV</h4>
                </div>
                <div class="modal-body">
                    <div class='row'>
                        <div class="col-md-12">
                            <div class="form-group">
                                {{ Form::label('start_date', 'Start Date') }}
                                <div class="input-group" id='start-date'>
                                    {{ Form::input('text', 'start_date', Input::old('start_date'), array('class' => 'form-control datepicker', 'readonly')) }}
                                    <span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>
                                </div>
                            </div>
                            <div class="form-group">
                                {{ Form::label('end_date', 'End Date') }}
                                <div class="input-group" id='end-date'>
                                    {{ Form::input('text', 'end_date', Input::old('end_date'), array('class' => 'form-control datepicker', 'readonly')) }}
                                    <span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>
                                </div>
                            </div>
                            <p class="help-block">Leave the dates empty to download all.</p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    {{ Form::submit('Download CSV', array('class' => 'btn btn-primary')) }}
                </div>
                {{ Form::close() }}
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
