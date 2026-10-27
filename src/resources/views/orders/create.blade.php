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

    {!! Form::open(array('files' => TRUE, 'action' => '\Redooor\Redminportal\App\Http\Controllers\OrderController@postStore', 'role' => 'form')) !!}
    
    <div class="row">
        <div class="col-lg-9">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">{{ Lang::get('redminportal::forms.create_order') }}</h4>
                </div>
                <div class="card-body">
                    {!! Redminportal::form()->emailInputer(old('email')) !!}
                    
                    <div class="mb-3">
                        {!! Form::label('payment_status', Lang::get('redminportal::forms.payment_status')) !!}
                        {!! Form::select('payment_status', $payment_statuses, null, array('class' => 'form-select')) !!}
                    </div>
                    <div class="mb-3">
                        {!! Form::label('paid', Lang::get('redminportal::forms.paid')) !!}
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            {!! Form::text('paid', null, array('class' => 'form-control', 'required')) !!}
                        </div>
                    </div>
                    <div class="mb-3">
                        {!! Form::label('transaction_id', Lang::get('redminportal::forms.transaction_id')) !!}
                        {!! Form::text('transaction_id', null, array('class' => 'form-control', 'required')) !!}
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
                        {!! Form::label('pricelist_id', Lang::get('redminportal::forms.membership_modules')) !!}
                        {!! Form::select('pricelist_id', $pricelists, null, array('class' => 'form-select', 'id' => 'pricelist_id', 'multiple', 'name' => 'pricelist_id[]')) !!}
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
                        {!! Form::label('coupon_id', Lang::get('redminportal::forms.coupons') . '(' . Lang::get('redminportal::forms.optional') . ')') !!}
                        {!! Form::select('coupon_id', $coupons, null, array('class' => 'form-select', 'id' => 'coupon_id', 'multiple', 'name' => 'coupon_id[]')) !!}
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
                    {!! HTML::link('admin/orders', Lang::get('redminportal::buttons.cancel'), array('class' => 'btn btn-link btn-sm'))!!}
                    {!! Form::submit(Lang::get('redminportal::buttons.create'), array('class' => 'btn btn-primary btn-sm float-end')) !!}
                </div>
            </div>
        </div>
    </div>
    {!! Form::close() !!}
@stop

@section('footer')
    @include('redminportal::plugins/tablesinput-orders')
@stop
