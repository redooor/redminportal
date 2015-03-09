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
        @if (count($coupons) > 0)
        <span class="label label-default pull-left">
            {{ $coupons->getFrom() . ' to ' . $coupons->getTo() . ' ( total ' . $coupons->getTotal() . ' )' }}
        </span>
        <br>
        @endif
        {{ HTML::link('admin/coupons/create', 'Create New', array('class' => 'btn btn-primary')) }}
    </div>
    
    @if (count($coupons) > 0)
        <table class='table table-striped table-bordered'>
            <thead>
                <tr>
                    <th>
                        <a href="{{ URL::to('admin/coupons/sort') . '/code/' . ($sortBy == 'code' && $orderBy == 'asc' ? 'desc' : 'asc') }}">
                            Coupon Code
                            @if ($sortBy == 'code')
                            {{ ($orderBy == 'asc' ? '<span class="caret"></span>' : '<span class="dropup"><span class="caret"></span></span>') }}
                            @endif
                        </a>
                    </th>
                    <th>
                        <a href="{{ URL::to('admin/coupons/sort') . '/amount/' . ($sortBy == 'amount' && $orderBy == 'asc' ? 'desc' : 'asc') }}">
                            Coupon Amount
                            @if ($sortBy == 'amount')
                            {{ ($orderBy == 'asc' ? '<span class="caret"></span>' : '<span class="dropup"><span class="caret"></span></span>') }}
                            @endif
                        </a>
                    </th>
                    <th>
                        <a href="{{ URL::to('admin/coupons/sort') . '/start_date/' . ($sortBy == 'start_date' && $orderBy == 'asc' ? 'desc' : 'asc') }}">
                            Coupon Start Date
                            @if ($sortBy == 'start_date')
                            {{ ($orderBy == 'asc' ? '<span class="caret"></span>' : '<span class="dropup"><span class="caret"></span></span>') }}
                            @endif
                        </a>
                    </th>
                    <th>
                        <a href="{{ URL::to('admin/coupons/sort') . '/end_date/' . ($sortBy == 'end_date' && $orderBy == 'asc' ? 'desc' : 'asc') }}">
                            Coupon Expiry Date
                            @if ($sortBy == 'end_date')
                            {{ ($orderBy == 'asc' ? '<span class="caret"></span>' : '<span class="dropup"><span class="caret"></span></span>') }}
                            @endif
                        </a>
                    </th>
                    <th>
                        <a href="{{ URL::to('admin/coupons/sort') . '/usage_limit_per_coupon/' . ($sortBy == 'usage_limit_per_coupon' && $orderBy == 'asc' ? 'desc' : 'asc') }}">
                            Usage Limit Per Coupon
                            @if ($sortBy == 'usage_limit_per_coupon')
                            {{ ($orderBy == 'asc' ? '<span class="caret"></span>' : '<span class="dropup"><span class="caret"></span></span>') }}
                            @endif
                        </a>
                    </th>
                    <th>
                        <a href="{{ URL::to('admin/coupons/sort') . '/usage_limit_per_user/' . ($sortBy == 'usage_limit_per_user' && $orderBy == 'asc' ? 'desc' : 'asc') }}">
                            Usage Limit Per User
                            @if ($sortBy == 'usage_limit_per_user')
                            {{ ($orderBy == 'asc' ? '<span class="caret"></span>' : '<span class="dropup"><span class="caret"></span></span>') }}
                            @endif
                        </a>
                    </th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>

                @foreach ($coupons as $coupon)
                    <tr>
                        <td>
                            {{ $coupon->code }}
                        </td>
                        <td>
                            {{ $coupon->amount }} @if($coupon->is_percent){{ "%" }}@else{{ "(fixed value)" }}@endif
                        </td>
                        <td>
                            {{ date("d/m/Y h:i A", strtotime($coupon->start_date)) }}
                        </td>
                        <td>
                            {{ date("d/m/Y h:i A", strtotime($coupon->end_date)) }}
                        </td>
                        <td>
                            {{ $coupon->usage_limit_per_coupon }}
                        </td>
                        <td>
                            {{ $coupon->usage_limit_per_user }}
                        </td>
                        <td>
                            <div class="btn-group">
                                <button type="button" class="btn btn-default btn-xs dropdown-toggle" data-toggle="dropdown">
                                    Action <span class="caret"></span>
                                </button>
                                <ul class="dropdown-menu pull-right" role="menu">
                                    <li>
                                        <a href="{{ URL::to('admin/coupons/edit/' . $coupon->id) }}">
                                            <i class="glyphicon glyphicon-edit"></i>Edit</a>
                                    </li>
                                    <li>
                                        <a href="{{ URL::to('admin/coupons/delete/' . $coupon->id) }}" class="btn-confirm">
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
        {{ $coupons->links() }}
        </div>
    @else
        <div class="alert alert-info">No coupon found</div>
    @endif
@stop

@section('footer')
@stop
