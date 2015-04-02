@extends('redminportal::layouts.master')

@section('content')
    @if (isset($errors))
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
    @endif

    <div class="nav-controls text-right">
        @if (count($memberships) > 0)
        <span class="label label-default pull-left">
            {{ $memberships->count() . ' to ' . $memberships->perPage() . ' ( total ' . $memberships->total() . ' )' }}
        </span>
        @endif
        {!! HTML::link('admin/memberships/create', 'Create New', array('class' => 'btn btn-primary')) !!}
    </div>

    @if (count($memberships) > 0)
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
        <div class="text-center">
        {!! $memberships->render() !!}
        </div>
    @else
        <div class="alert alert-info">No membership found</div>
    @endif
@stop
