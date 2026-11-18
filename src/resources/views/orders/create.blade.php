@extends('redminportal::layouts.master')

@section('navbar-breadcrumb')
    <li class="nav-item"><a class="nav-link" href="{{ URL::to('admin/orders') }}">{{ Lang::get('redminportal::menus.orders') }}</a></li>
    <li class="nav-item"><span class="nav-link active">{{ Lang::get('redminportal::forms.create') }}</span></li>
@stop

@section('head')
<style>
    .table>tbody>tr>td, .table>tbody>tr>th, .table>tfoot>tr>td, .table>tfoot>tr>th, .table>thead>tr>td, .table>thead>tr>th {
        vertical-align: middle;
    }
</style>
@stop

@section('content')
    
    @include('redminportal::partials.errors')

    <form method="POST" action="{{ action('\Redooor\Redminportal\App\Http\Controllers\OrderController@postStore') }}" enctype="multipart/form-data" role="form">
    @csrf
    
    <div class="row">
        <div class="col-lg-9">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">{{ Lang::get('redminportal::forms.create_order') }}</h4>
                </div>
                <div class="card-body">
                    {!! Redminportal::form()->emailInputer(old('email')) !!}
                    
                    <div class="mb-3">
                        <label for="payment_status">{{ Lang::get('redminportal::forms.payment_status') }}</label>
                        <select name="payment_status" id="payment_status" class="form-select">@foreach ($payment_statuses as $k => $label)<option value="{{ $k }}" @selected((string) $k === (string) old('payment_status', null))>{{ $label }}</option>@endforeach</select>
                    </div>
                    <div class="mb-3">
                        <label for="paid">{{ Lang::get('redminportal::forms.paid') }}</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="text" name="paid" id="paid" value="{{ old('paid', null) }}" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="transaction_id">{{ Lang::get('redminportal::forms.transaction_id') }}</label>
                        <input type="text" name="transaction_id" id="transaction_id" value="{{ old('transaction_id', null) }}" class="form-control" required>
                    </div>
                </div>
            </div>
            @if (count($products) > 0)
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">{{ trans('redminportal::forms.add_products') }}</h4>
                </div>
                <div class="card-body">
                    <table class="table table-striped table-sm" id="selected_products_table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Quantity</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="4"><i>{{ trans('redminportal::messages.help_product_builder') }}</i></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <div class="card-footer">
                    <div class="row">
                        <div class="col-lg-8">
                            <div class="mb-3">
                                <label>{{ trans('redminportal::forms.products') }}*</label>
                                <select id="select_product" multiple class="form-control">
                                    @foreach ($products as $product_id => $product_name)
                                    <option value="{{ $product_id }}">{{ $product_name }}</option>
                                    @endforeach
                                </select>
                                <p class="form-text">
                                    * {{ trans('redminportal::messages.allow_select_multiple') }}<br>
                                    * {{ trans('redminportal::messages.how_to_deselect_multiple') }}
                                </p>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="mb-3">
                                <label for="product_quantity">{{ trans('redminportal::forms.product_quantity') }}</label>
                                {!! Redminportal::form()->inputer('product_quantity', old('product_quantity'), ['class' => 'tagsinput', 'value' => 1, 'min' => 1, 'type' => 'number']) !!}
                                <p class="form-text">{{ trans('redminportal::messages.help_product_builder_quantity') }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-12 text-end">
                            <a class="btn btn-primary btn-sm btn-add-order" 
                               data-target-table="#selected_products_table" 
                               data-target-select="#select_product" 
                               data-target-input="#product_quantity" 
                               data-target-name="selected_products">{{ trans('redminportal::buttons.insert') }}</a>
                        </div>
                    </div>
                </div>
            </div>
            @else
            <div class="card border-warning">
                <div class="card-header">
                    <h4 class="card-title mb-0">{{ trans('redminportal::forms.products') }}</h4>
                </div>
                <div class="card-footer">
                    {{ Lang::get('redminportal::messages.no_product_found') }}
                </div>
            </div>
            @endif
            @if (count($bundles) > 0)
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">{{ trans('redminportal::forms.add_bundles') }}</h4>
                </div>
                <div class="card-body">
                    <table class="table table-striped table-sm" id="selected_bundles_table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Quantity</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="4"><i>{{ trans('redminportal::messages.help_bundle_builder') }}</i></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <div class="card-footer">
                    <div class="row">
                        <div class="col-lg-8">
                            <div class="mb-3">
                                <label>{{ trans('redminportal::forms.bundles') }}*</label>
                                <select id="select_bundle" multiple class="form-control">
                                    @foreach ($bundles as $bundle_id => $bundle_name)
                                    <option value="{{ $bundle_id }}">{{ $bundle_name }}</option>
                                    @endforeach
                                </select>
                                <p class="form-text">
                                    * {{ trans('redminportal::messages.allow_select_multiple') }}<br>
                                    * {{ trans('redminportal::messages.how_to_deselect_multiple') }}
                                </p>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="mb-3">
                                <label for="bundle_quantity">{{ trans('redminportal::forms.bundle_quantity') }}</label>
                                {!! Redminportal::form()->inputer('bundle_quantity', old('bundle_quantity'), ['class' => 'tagsinput', 'value' => 1, 'min' => 1, 'type' => 'number']) !!}
                                <p class="form-text">{{ trans('redminportal::messages.help_bundle_builder_quantity') }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-12 text-end">
                            <a class="btn btn-primary btn-sm btn-add-order" 
                               data-target-table="#selected_bundles_table" 
                               data-target-select="#select_bundle" 
                               data-target-input="#bundle_quantity" 
                               data-target-name="selected_bundles">{{ trans('redminportal::buttons.insert') }}</a>
                        </div>
                    </div>
                </div>
            </div>
            @else
            <div class="card border-warning">
                <div class="card-header">
                    <h4 class="card-title mb-0">{{ trans('redminportal::forms.bundles') }}</h4>
                </div>
                <div class="card-footer">
                    {{ Lang::get('redminportal::messages.no_bundle_found') }}
                </div>
            </div>
            @endif
            @if (count($pricelists) > 0)
             <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">{{ Lang::get('redminportal::forms.add_membership_modules') }}</h4>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="pricelist_id">{{ Lang::get('redminportal::forms.membership_modules') }}</label>
                        <select name="pricelist_id[]" id="pricelist_id" class="form-select" multiple>@foreach ($pricelists as $k => $label)<option value="{{ $k }}" @selected(in_array((string) $k, array_map('strval', (array) old('pricelist_id', null)), true))>{{ $label }}</option>@endforeach</select>
                    </div>
                </div>
                <div class="card-footer">
                    <p class="form-text">{{ Lang::get('redminportal::messages.allow_select_multiple') }}</p>
                    <p class="form-text">{{ Lang::get('redminportal::messages.how_to_deselect_multiple') }}</p>
                </div>
            </div>
            @else
            <div class="card border-warning">
                <div class="card-header">
                    <h4 class="card-title mb-0">{{ Lang::get('redminportal::forms.membership_modules') }}</h4>
                </div>
                <div class="card-footer">{{ Lang::get('redminportal::messages.no_pricelist_found') }}</div>
            </div>
            @endif
            @if (count($coupons) > 0)
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">{{ Lang::get('redminportal::forms.apply_coupon') }}</h4>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="coupon_id">{{ Lang::get('redminportal::forms.coupons') . '(' . Lang::get('redminportal::forms.optional') . ')' }}</label>
                        <select name="coupon_id[]" id="coupon_id" class="form-select" multiple>@foreach ($coupons as $k => $label)<option value="{{ $k }}" @selected(in_array((string) $k, array_map('strval', (array) old('coupon_id', null)), true))>{{ $label }}</option>@endforeach</select>
                    </div>
                </div>
                <div class="card-footer">
                    <p class="form-text">{{ Lang::get('redminportal::messages.coupon_restriction_applies') }}</p>
                </div>
            </div>
            @else
            <div class="card border-warning">
                <div class="card-header">
                    <h4 class="card-title mb-0">{{ Lang::get('redminportal::forms.apply_coupon') }}</h4>
                </div>
                <div class="card-footer">{{ Lang::get('redminportal::messages.no_coupon_found') }}</div>
            </div>
            @endif
        </div>
        <div class="col-lg-3">
            <div class="card card-body bg-body-tertiary">
                <div class='form-actions'>
                    <a href="{{ URL::to('admin/orders') }}" class="btn btn-link btn-sm">{{ Lang::get('redminportal::buttons.cancel') }}</a>
                    <input type="submit" value="{{ Lang::get('redminportal::buttons.create') }}" class="btn btn-primary btn-sm float-end">
                </div>
            </div>
        </div>
    </div>
    </form>
@stop

@section('footer')
    @include('redminportal::plugins/tablesinput-orders')
@stop
