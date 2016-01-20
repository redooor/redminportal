@extends('redminportal::layouts.master')

@section('navbar-breadcrumb')
    <li><a href="{{ URL::to('admin/orders') }}">{{ Lang::get('redminportal::menus.orders') }}</a></li>
    <li class="active"><span class="navbar-text">{{ Lang::get('redminportal::forms.create') }}</span></li>
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
                    {!! Redminportal::form()->emailInputer() !!}
                    
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
                    <div class="form-group">
                        @if (count($products) > 0)
                            {!! Form::label('product_id', Lang::get('redminportal::forms.products')) !!}
                            {!! Form::select('product_id', $products, null, array('class' => 'form-control', 'id' => 'product_id', 'multiple', 'name' => 'product_id[]')) !!}
                        @else
                            <div class="alert alert-warning">{{ Lang::get('redminportal::messages.no_product_found') }}</div>
                        @endif
                    </div>
                    <div class="form-group">
                        @if (count($bundles) > 0)
                            {!! Form::label('bundle_id', Lang::get('redminportal::forms.bundles')) !!}
                            {!! Form::select('bundle_id', $bundles, null, array('class' => 'form-control', 'id' => 'bundle_id', 'multiple', 'name' => 'bundle_id[]')) !!}
                        @else
                            <div class="alert alert-warning">{{ Lang::get('redminportal::messages.no_bundle_found') }}</div>
                        @endif
                    </div>
                    <div class="form-group">
                        @if (count($pricelists) > 0)
                            {!! Form::label('pricelist_id', Lang::get('redminportal::forms.membership_modules')) !!}
                            {!! Form::select('pricelist_id', $pricelists, null, array('class' => 'form-control', 'id' => 'pricelist_id', 'multiple', 'name' => 'pricelist_id[]')) !!}
                        @else
                            <div class="alert alert-warning">{{ Lang::get('redminportal::messages.no_pricelist_found') }}</div>
                        @endif
                    </div>
                </div>
                <div class="panel-footer">
                    <p class="help-block">{{ Lang::get('redminportal::messages.allow_select_multiple') }}</p>
                    <p class="help-block">{{ Lang::get('redminportal::messages.how_to_deselect_multiple') }}</p>
                </div>
            </div>
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4 class="panel-title">{{ Lang::get('redminportal::forms.apply_coupon') }}</h4>
                </div>
                <div class="panel-body">
                    <div class="form-group">
                        @if (count($coupons) > 0)
                            {!! Form::label('coupon_id', Lang::get('redminportal::forms.coupons') . '(' . Lang::get('redminportal::forms.optional') . ')') !!}
                            {!! Form::select('coupon_id', $coupons, null, array('class' => 'form-control', 'id' => 'coupon_id', 'multiple', 'name' => 'coupon_id[]')) !!}
                        @else
                            <div class="alert alert-warning">{{ Lang::get('redminportal::messages.no_coupon_found') }}</div>
                        @endif
                    </div>
                </div>
                <div class="panel-footer">
                    <p class="help-block">{{ Lang::get('redminportal::messages.coupon_restriction_applies') }}</p>
                </div>
            </div>
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
