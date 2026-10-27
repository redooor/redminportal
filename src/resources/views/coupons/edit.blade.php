@extends('redminportal::layouts.master')

@section('head')
    <link rel="stylesheet" href="{{ URL::to('vendor/redooor/redminportal/css/tempus-dominus/tempus-dominus.min.css') }}">
@stop

@section('navbar-breadcrumb')
    <li class="nav-item"><a class="nav-link" href="{{ URL::to('admin/coupons') }}">{{ Lang::get('redminportal::menus.coupons') }}</a></li>
    <li class="nav-item"><span class="nav-link active">{{ Lang::get('redminportal::forms.edit') }}</span></li>
@stop

@section('content')
    
    @include('redminportal::partials.errors')
    
    {!! Form::open(array('action' => '\Redooor\Redminportal\App\Http\Controllers\CouponController@postStore', 'role' => 'form', 'id' => 'form_add')) !!}
        {!! Form::hidden('id', $coupon->id) !!}
        <div class='row'>
	        <div class="col-lg-9">
                <div>
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title mb-0">{{ Lang::get('redminportal::forms.coupon_data') }}</h4>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <label class="col-form-label text-lg-end col-lg-4">{{ Lang::get('redminportal::forms.coupon_code') }}</label>
                                <div class="col-lg-8">
                                    {!! Form::text('code', $coupon->code, array('class' => 'form-control')) !!}
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label class="col-form-label text-lg-end col-lg-4">{{ Lang::get('redminportal::forms.description') . ' (' . Lang::get('redminportal::forms.optional') . ')' }}</label>
                                <div class="col-lg-8">
                                    {!! Form::textarea('description', $coupon->description, array('class' => 'form-control', 'rows' => '3')) !!}
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label class="col-form-label text-lg-end col-lg-4">{{ Lang::get('redminportal::forms.amount') }}</label>
                                <div class="col-lg-8">
                                    {!! Form::text('amount', $coupon->amount, array('class' => 'form-control')) !!}
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="is_percent" class="col-form-label text-lg-end col-lg-4">{{ Lang::get('redminportal::forms.is_percent') }}</label>
                                <div class="col-lg-8">
                                    <div class="form-check">
                                        <label class="form-check-label">
                                            {!! Form::checkbox('is_percent', $coupon->is_percent, $coupon->is_percent, array('class' => 'form-check-input', 'id' => 'is_percent', 'name' => 'is_percent')) !!}
                                            <i>{{ Lang::get('redminportal::forms.discount_explanation') }}</i>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label class="col-form-label text-lg-end col-lg-4">{{ Lang::get('redminportal::forms.start_date') }}</label>
                                <div class="col-lg-8">
                                    <div class="input-group" id="start-date" data-td-target-input="nearest" data-td-target-toggle="nearest">
                                        {!! Form::input('text', 'start_date', date("d/m/Y h:i A", strtotime($coupon->start_date)), array('class' => 'form-control', 'data-td-target' => '#start-date', 'autocomplete' => 'off')) !!}
                                        <span class="input-group-text" data-td-target="#start-date" data-td-toggle="datetimepicker"><i class="fa-solid fa-calendar"></i></span>
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label class="col-form-label text-lg-end col-lg-4">{{ Lang::get('redminportal::forms.expiry_date') }}</label>
                                <div class="col-lg-8">
                                    <div class="input-group" id="end-date" data-td-target-input="nearest" data-td-target-toggle="nearest">
                                        {!! Form::input('text', 'end_date', date("d/m/Y h:i A", strtotime($coupon->end_date)), array('class' => 'form-control', 'data-td-target' => '#end-date', 'autocomplete' => 'off')) !!}
                                        <span class="input-group-text" data-td-target="#end-date" data-td-toggle="datetimepicker"><i class="fa-solid fa-calendar"></i></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title mb-0">{{ Lang::get('redminportal::forms.usage_restrictions') }}</h4>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <label for="multiple_coupons" class="col-form-label text-lg-end col-lg-4">{{ Lang::get('redminportal::forms.multiple_coupons') }}</label>
                                <div class="col-lg-8">
                                    <div class="form-check">
                                        <label class="form-check-label">
                                            {!! Form::checkbox('multiple_coupons', $coupon->multiple_coupons, $coupon->multiple_coupons, array('class' => 'form-check-input', 'id' => 'multiple_coupons')) !!}
                                            <i>{{ Lang::get('redminportal::forms.multiple_coupons_explanation') }}</i>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="is_percent" class="col-form-label text-lg-end col-lg-4">{{ Lang::get('redminportal::forms.exclude_sale_item') }}</label>
                                <div class="col-lg-8">
                                    <div class="form-check">
                                        <label class="form-check-label">
                                            {!! Form::checkbox('exclude_sale_item', $coupon->exclude_sale_item, $coupon->exclude_sale_item, array('class' => 'form-check-input', 'id' => 'exclude_sale_item')) !!}
                                            <i>{{ Lang::get('redminportal::forms.exclude_sale_item_explanation') }}</i>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="is_percent" class="col-form-label text-lg-end col-lg-4">{{ Lang::get('redminportal::forms.automatically_apply') }}</label>
                                <div class="col-lg-8">
                                    <div class="form-check">
                                        <label class="form-check-label">
                                            {!! Form::checkbox('automatically_apply', $coupon->automatically_apply, $coupon->automatically_apply, array('class' => 'form-check-input', 'id' => 'automatically_apply')) !!}
                                            <i>{{ Lang::get('redminportal::forms.automatically_apply_explanation') }}</i>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label class="col-form-label text-lg-end col-lg-4">{{ Lang::get('redminportal::forms.min_spent') }}</label>
                                <div class="col-lg-8">
                                    {!! Form::text('min_spent', $coupon->min_spent, array('class' => 'form-control')) !!}
                                    <p class="form-text">{{ Lang::get('redminportal::messages.applies_to_whole_cart_spent') }}</p>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label class="col-form-label text-lg-end col-lg-4">{{ Lang::get('redminportal::forms.max_spent') }}</label>
                                <div class="col-lg-8">
                                    {!! Form::text('max_spent', $coupon->max_spent, array('class' => 'form-control')) !!}
                                    <p class="form-text">{{ Lang::get('redminportal::messages.applies_to_whole_cart_spent') }}</p>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label class="col-form-label text-lg-end col-lg-4">{{ Lang::get('redminportal::forms.restricted_to') }}</label>
                                <div class="col-lg-8">
                                    <h4>{{ Lang::get('redminportal::forms.categories') }}</h4>
                                    @if (count($categories) > 0)
                                    <p></p>
                                    {!! Form::select('category_id', $categories, $category_id, array('class' => 'form-select', 'id' => 'category_id', 'multiple', 'name' => 'category_id[]')) !!}
                                    @else
                                    <div class="alert alert-warning">{{ Lang::get('redminportal::messages.no_category_found') }}</div>
                                    @endif
                                    <h4>{{ Lang::get('redminportal::forms.products') }}</h4>
                                    @if (count($products) > 0)
                                    <p></p>
                                    {!! Form::select('product_id', $products, $product_id, array('class' => 'form-select', 'id' => 'product_id', 'multiple', 'name' => 'product_id[]')) !!}
                                    @else
                                    <div class="alert alert-warning">{{ Lang::get('redminportal::messages.no_product_found') }}</div>
                                    @endif
                                    <h4>{{ Lang::get('redminportal::forms.membership_modules') }}</h4>
                                    @if (count($membermodules) > 0)
                                    <p></p>
                                    {!! Form::select('pricelist_id', $membermodules, $pricelist_id, array('class' => 'form-select', 'id' => 'pricelist_id', 'multiple', 'name' => 'pricelist_id[]')) !!}
                                    @else
                                    <div class="alert alert-warning">{{ Lang::get('redminportal::messages.no_pricelist_found') }}</div>
                                    @endif
                                    <h4>{{ Lang::get('redminportal::forms.bundles') }}</h4>
                                    @if (count($bundles) > 0)
                                    <p></p>
                                    {!! Form::select('bundle_id', $bundles, $bundle_id, array('class' => 'form-select', 'id' => 'bundle_id', 'multiple', 'name' => 'bundle_id[]')) !!}
                                    @else
                                    <div class="alert alert-warning">{{ Lang::get('redminportal::messages.no_bundle_found') }}</div>
                                    @endif
                                    <p class="form-text">{{ Lang::get('redminportal::messages.product_level_supersede_category') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title mb-0">{{ Lang::get('redminportal::forms.usage_limit') }}</h4>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <label class="col-form-label text-lg-end col-lg-4">{{ Lang::get('redminportal::forms.usage_limit_per_coupon') }}</label>
                                <div class="col-lg-8">
                                    {!! Form::text('usage_limit_per_coupon', $coupon->usage_limit_per_coupon, array('class' => 'form-control')) !!}
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label class="col-form-label text-lg-end col-lg-4">{{ Lang::get('redminportal::forms.usage_limit_per_user') }}</label>
                                <div class="col-lg-8">
                                    {!! Form::text('usage_limit_per_user', $coupon->usage_limit_per_user, array('class' => 'form-control')) !!}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
	        </div>
            <div class="col-lg-3">
                <div class="card card-body bg-body-tertiary">
                    <div class='form-actions'>
                        {!! HTML::link('admin/coupons', Lang::get('redminportal::buttons.cancel'), array('class' => 'btn btn-link btn-sm'))!!}
                        {!! Form::submit(Lang::get('redminportal::buttons.save'), array('class' => 'btn btn-primary btn-sm float-end')) !!}
                    </div>
                </div>
	        </div>
        </div>
    {!! Form::close() !!}
@stop

@section('footer')
    <script src="{{ URL::to('vendor/redooor/redminportal/js/popper/popper.min.js') }}"></script>
    <script src="{{ URL::to('vendor/redooor/redminportal/js/tempus-dominus/tempus-dominus.min.js') }}"></script>
    <script>
        // Coupon dates are posted as d/m/Y h:i A (see CouponController).
        document.querySelectorAll('#start-date, #end-date').forEach(function (el) {
            new tempusDominus.TempusDominus(el, {
                useCurrent: false,
                // Fixed locale: the server parses d/m/Y h:i A, so AM/PM and digits must not follow the browser language.
                localization: { locale: 'en-US', hourCycle: 'h12', format: 'dd/MM/yyyy hh:mm T' },
                display: {
                    theme: 'light',
                    components: { seconds: false },
                    buttons: { today: true }
                }
            });
        });
    </script>
@stop
