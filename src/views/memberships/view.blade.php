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
        @if ($memberships)
        <span class="label label-default pull-left">
            {{ $memberships->getFrom() . ' to ' . $memberships->getTo() . ' ( total ' . $memberships->getTotal() . ' )' }}
        </span>
        @endif
        {{ HTML::link('admin/memberships/create', 'Create New', array('class' => 'btn btn-primary')) }}
    </div>

    @if ($memberships)
        <table class='table table-striped table-bordered'>
            <thead>
                <tr>
                    <th>Rank</th>
                    <th>Name</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            @foreach ($memberships as $membership)
                <tr>
                    <td>{{ $membership->rank }}</td>
                    <td>{{ $membership->name }}</td>
                    <td>
                        <div class="btn-group">
                            <button type="button" class="btn btn-default btn-xs dropdown-toggle" data-toggle="dropdown">
                                Action <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu pull-right" role="menu">
                                <li>
                                    <a href="{{ URL::to('admin/memberships/edit/' . $membership->id) }}">
                                        <i class="glyphicon glyphicon-edit"></i>Edit</a>
                                </li>
                                <li>
                                    <a href="{{ URL::to('admin/memberships/delete/' . $membership->id) }}" class="btn-confirm">
                                        <i class="glyphicon glyphicon-remove"></i>Delete</a>
                                </li>
                            </ul>
                        </div>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        {{ $memberships->links() }}
    @else
        <p>No membership found</p>
    @endif
@stop

@section('footer')
    <script>
        !function ($) {
            $(function(){
                // Add pagination class to ul
                $('div.pagination > ul').addClass('pagination');
                $('div.pagination').removeClass('pagination').addClass('text-center');
            })
        }(window.jQuery);
    </script>
@stop
