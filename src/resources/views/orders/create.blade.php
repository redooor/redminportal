@extends('redminportal::layouts.master')

@section('navbar-breadcrumb')
    <li><a href="{{ URL::to('admin/orders') }}">{{ Lang::get('redminportal::menus.orders') }}</a></li>
    <li class="active"><span class="navbar-text">{{ Lang::get('redminportal::forms.create') }}</span></li>
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
        <div class="col-md-9">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4 class="panel-title">{{ Lang::get('redminportal::forms.create_order') }}</h4>
                </div>
                <div class="panel-body">
                    {!! Redminportal::form()->emailInputer(old('email')) !!}
                    
                    <div class="form-group">
                        {!! Form::label('payment_status', Lang::get('redminportal::forms.payment_status')) !!}
                        {!! Form::select('payment_status', $payment_statuses, null, array('class' => 'form-control')) !!}
                    </div>
                    <div class="form-group">
                        {!! Form::label('paid', Lang::get('redminportal::forms.paid')) !!}
                        <div class="input-group">
                            <span class="input-group-addon">$</span>
                            {!! Form::text('paid', null, array('class' => 'form-control', 'required')) !!}
                        </div>
                    </div>
                    <div class="form-group">
                        {!! Form::label('transaction_id', Lang::get('redminportal::forms.transaction_id')) !!}
                        {!! Form::text('transaction_id', null, array('class' => 'form-control', 'required')) !!}
                    </div>
                </div>
            </div>
            @if (count($products) > 0)
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4 class="panel-title">{{ trans('redminportal::forms.add_products') }}</h4>
                </div>
                <div class="panel-body">
                    <table class="table table-striped table-condensed" id="selected_products_table">
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
                <div class="panel-footer">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label>{{ trans('redminportal::forms.products') }}*</label>
                                <select id="select_product" multiple class="form-control">
                                    @foreach ($products as $product_id => $product_name)
                                    <option value="{{ $product_id }}">{{ $product_name }}</option>
                                    @endforeach
                                </select>
                                <p class="help-block">
                                    * {{ trans('redminportal::messages.allow_select_multiple') }}<br>
                                    * {{ trans('redminportal::messages.how_to_deselect_multiple') }}
                                </p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="product_quantity">{{ trans('redminportal::forms.product_quantity') }}</label>
                                {!! Redminportal::form()->inputer('product_quantity', old('product_quantity'), ['class' => 'tagsinput', 'value' => 1, 'min' => 1, 'type' => 'number']) !!}
                                <p class="help-block">{{ trans('redminportal::messages.help_product_builder_quantity') }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 text-right">
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
            <div class="panel panel-warning">
                <div class="panel-heading">
                    <h4 class="panel-title">{{ trans('redminportal::forms.products') }}</h4>
                </div>
                <div class="panel-footer">
                    {{ Lang::get('redminportal::messages.no_product_found') }}
                </div>
            </div>
            @endif
            @if (count($bundles) > 0)
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4 class="panel-title">{{ trans('redminportal::forms.add_bundles') }}</h4>
                </div>
                <div class="panel-body">
                    <table class="table table-striped table-condensed" id="selected_bundles_table">
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
                <div class="panel-footer">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label>{{ trans('redminportal::forms.bundles') }}*</label>
                                <select id="select_bundle" multiple class="form-control">
                                    @foreach ($bundles as $bundle_id => $bundle_name)
                                    <option value="{{ $bundle_id }}">{{ $bundle_name }}</option>
                                    @endforeach
                                </select>
                                <p class="help-block">
                                    * {{ trans('redminportal::messages.allow_select_multiple') }}<br>
                                    * {{ trans('redminportal::messages.how_to_deselect_multiple') }}
                                </p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="bundle_quantity">{{ trans('redminportal::forms.bundle_quantity') }}</label>
                                {!! Redminportal::form()->inputer('bundle_quantity', old('bundle_quantity'), ['class' => 'tagsinput', 'value' => 1, 'min' => 1, 'type' => 'number']) !!}
                                <p class="help-block">{{ trans('redminportal::messages.help_bundle_builder_quantity') }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 text-right">
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
            <div class="panel panel-warning">
                <div class="panel-heading">
                    <h4 class="panel-title">{{ trans('redminportal::forms.bundles') }}</h4>
                </div>
                <div class="panel-footer">
                    {{ Lang::get('redminportal::messages.no_bundle_found') }}
                </div>
            </div>
            @endif
            @if (count($pricelists) > 0)
             <div class="panel panel-default">
                <div class="panel-heading">
                    <h4 class="panel-title">{{ Lang::get('redminportal::forms.add_membership_modules') }}</h4>
                </div>
                <div class="panel-body">
                    <div class="form-group">
                        {!! Form::label('pricelist_id', Lang::get('redminportal::forms.membership_modules')) !!}
                        {!! Form::select('pricelist_id', $pricelists, null, array('class' => 'form-control', 'id' => 'pricelist_id', 'multiple', 'name' => 'pricelist_id[]')) !!}
                    </div>
                </div>
                <div class="panel-footer">
                    <p class="help-block">{{ Lang::get('redminportal::messages.allow_select_multiple') }}</p>
                    <p class="help-block">{{ Lang::get('redminportal::messages.how_to_deselect_multiple') }}</p>
                </div>
            </div>
            @else
            <div class="panel panel-warning">
                <div class="panel-heading">
                    <h4 class="panel-title">{{ Lang::get('redminportal::forms.membership_modules') }}</h4>
                </div>
                <div class="panel-footer">{{ Lang::get('redminportal::messages.no_pricelist_found') }}</div>
            </div>
            @endif
            @if (count($coupons) > 0)
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4 class="panel-title">{{ Lang::get('redminportal::forms.apply_coupon') }}</h4>
                </div>
                <div class="panel-body">
                    <div class="form-group">
                        {!! Form::label('coupon_id', Lang::get('redminportal::forms.coupons') . '(' . Lang::get('redminportal::forms.optional') . ')') !!}
                        {!! Form::select('coupon_id', $coupons, null, array('class' => 'form-control', 'id' => 'coupon_id', 'multiple', 'name' => 'coupon_id[]')) !!}
                    </div>
                </div>
                <div class="panel-footer">
                    <p class="help-block">{{ Lang::get('redminportal::messages.coupon_restriction_applies') }}</p>
                </div>
            </div>
            @else
            <div class="panel panel-warning">
                <div class="panel-heading">
                    <h4 class="panel-title">{{ Lang::get('redminportal::forms.apply_coupon') }}</h4>
                </div>
                <div class="panel-footer">{{ Lang::get('redminportal::messages.no_coupon_found') }}</div>
            </div>
            @endif
        </div>
        <div class="col-md-3">
            <div class="well">
                <div class='form-actions'>
                    {!! HTML::link('admin/orders', Lang::get('redminportal::buttons.cancel'), array('class' => 'btn btn-link btn-sm'))!!}
                    {!! Form::submit(Lang::get('redminportal::buttons.create'), array('class' => 'btn btn-primary btn-sm pull-right')) !!}
                </div>
            </div>
        </div>
    </div>
    {!! Form::close() !!}
@stop

@section('footer')
    @include('redminportal::plugins/tablesinput-orders')
@stop
