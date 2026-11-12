@extends('redminportal::layouts.master')

@section('navbar-breadcrumb')
    <li class="nav-item"><span class="nav-link active">{{ Lang::get('redminportal::menus.coupons') }}</span></li>
@stop

@section('content')
    @include('redminportal::partials.confirm-delete')
    
    @include('redminportal::partials.errors')

    @include('redminportal::partials.html-view-nav-controls', ['models' => $models, 'model_view' => 'coupons'])
    
    @if (count($models) > 0)
        <div class="table-responsive">
        <table class='table table-striped table-sm'>
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
                        <td class="table-actions text-end">
                            @permitted('admin/coupons/edit/' . $coupon->id, 'admin/coupons/delete/' . $coupon->id)
                            <div class="btn-group">
                                <button type="button" class="btn btn-link dropdown-toggle" data-bs-toggle="dropdown" data-bs-popper-config='{"strategy":"fixed"}'>
                                    <i class="fa-solid fa-ellipsis"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    @permitted('admin/coupons/edit/' . $coupon->id)
                                    <li>
                                        <a class="dropdown-item" href="{{ URL::to('admin/coupons/edit/' . $coupon->id) }}">
                                            <i class="fa-solid fa-pen-to-square"></i>{{ Lang::get('redminportal::buttons.edit') }}</a>
                                    </li>
                                    @endpermitted
                                    @permitted('admin/coupons/delete/' . $coupon->id)
                                    <li>
                                        <a class="dropdown-item btn-confirm" href="{{ URL::to('admin/coupons/delete/' . $coupon->id) }}">
                                            <i class="fa-solid fa-xmark"></i>{{ Lang::get('redminportal::buttons.delete') }}</a>
                                    </li>
                                    @endpermitted
                                </ul>
                            </div>
                            @endpermitted
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        </div>
        <div class="text-center">
        {!! $models->render('pagination::bootstrap-5') !!}
        </div>
    @else
        @if ($models->total() > 0)
        <div class="p-3 rounded bg-body-secondary text-body-secondary">{{ Lang::get('redminportal::messages.no_record_page_empty') }}</div>
        <a href="{{ $models->url($models->lastPage()) }}" class="btn btn-secondary"><i class="fa-solid fa-chevron-left"></i> {{ Lang::get('redminportal::buttons.previous_page') }}</a>
        @else
        <div class="p-3 rounded bg-body-secondary text-body-secondary">{{ Lang::get('redminportal::messages.no_coupon_found') }}</div>
        @endif
    @endif
@stop

@section('footer')
@stop
