@extends('redminportal::layouts.master')

@section('navbar-breadcrumb')
    <li class="active"><span class="navbar-text">{{ Lang::get('redminportal::menus.coupons') }}</span></li>
@stop

@section('content')
    
    @include('redminportal::partials.errors')

    <div class="row">
        <div class="col-md-12">
            <div class="nav-controls text-right">
                <div class="btn-group" role="group">
                @if (count($models) > 0)
                <a href="" class="btn btn-default btn-sm disabled btn-text">{{ $models->firstItem() . ' to ' . $models->lastItem() . ' of ' . $models->total() }}</a>
                @endif
                {!! HTML::link('admin/coupons/create', Lang::get('redminportal::buttons.create_new'), array('class' => 'btn btn-primary btn-sm')) !!}
            </div>
            </div>
        </div>
    </div>
    
    @if (count($models) > 0)
        <table class='table table-striped table-bordered table-condensed'>
            <thead>
                <tr>
                    <th>
                        {!! Redminportal::html()->sorter('admin/coupons', 'code', $sortBy, $orderBy, trans('redminportal::forms.coupon_code')) !!}
                    </th>
                    <th>
                        {!! Redminportal::html()->sorter('admin/coupons', 'amount', $sortBy, $orderBy) !!}
                    </th>
                    <th>
                        {!! Redminportal::html()->sorter('admin/coupons', 'start_date', $sortBy, $orderBy) !!}
                    </th>
                    <th>
                        {!! Redminportal::html()->sorter('admin/coupons', 'end_date', $sortBy, $orderBy) !!}
                    </th>
                    <th>
                        {!! Redminportal::html()->sorter('admin/coupons', 'usage_limit_per_coupon', $sortBy, $orderBy) !!}
                    </th>
                    <th>
                        {!! Redminportal::html()->sorter('admin/coupons', 'usage_limit_per_user', $sortBy, $orderBy) !!}
                    </th>
                    <th>
                        {!! Redminportal::html()->sorter('admin/coupons', 'usage_limit_per_coupon_count', $sortBy, $orderBy) !!}
                    </th>
                    <th></th>
                </tr>
            </thead>
            <tbody>

                @foreach ($models as $coupon)
                    <tr>
                        <td>
                            {{ $coupon->code }}
                        </td>
                        <td>
                            {{ $coupon->amount }} @if($coupon->is_percent){{ "%" }}@else{{ "(fixed value)" }}@endif
                        </td>
                        <td>
                            {{ date("d/m/y h:i A", strtotime($coupon->start_date)) }}
                        </td>
                        <td>
                            {{ date("d/m/y h:i A", strtotime($coupon->end_date)) }}
                        </td>
                        <td>
                            {{ $coupon->usage_limit_per_coupon }}
                        </td>
                        <td>
                            {{ $coupon->usage_limit_per_user }}
                        </td>
                        <td>
                            {{ $coupon->usage_limit_per_coupon_count }}
                        </td>
                        <td class="table-actions text-right">
                            <div class="btn-group">
                                <button type="button" class="btn btn-link dropdown-toggle" data-toggle="dropdown">
                                    <span class="glyphicon glyphicon-option-horizontal"></span>
                                </button>
                                <ul class="dropdown-menu pull-right" role="menu">
                                    <li>
                                        <a href="{{ URL::to('admin/coupons/edit/' . $coupon->id) }}">
                                            <i class="glyphicon glyphicon-edit"></i>{{ Lang::get('redminportal::buttons.edit') }}</a>
                                    </li>
                                    <li>
                                        <a href="{{ URL::to('admin/coupons/delete/' . $coupon->id) }}" class="btn-confirm">
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
        <div class="alert alert-info">{{ Lang::get('redminportal::messages.no_coupon_found') }}</div>
        @endif
    @endif
@stop

@section('footer')
@stop
