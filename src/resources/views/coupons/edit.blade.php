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
    
    <form method="POST" action="{{ action('\Redooor\Redminportal\App\Http\Controllers\CouponController@postStore') }}" role="form" id="form_add">
    @csrf
        <input type="hidden" name="id" value="{{ old('id', $coupon->id) }}">
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
                                    <input type="text" name="code" value="{{ old('code', $coupon->code) }}" class="form-control">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label class="col-form-label text-lg-end col-lg-4">{{ Lang::get('redminportal::forms.description') . ' (' . Lang::get('redminportal::forms.optional') . ')' }}</label>
                                <div class="col-lg-8">
                                    <textarea name="description" class="form-control" rows="3">{{ old('description', $coupon->description) }}</textarea>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label class="col-form-label text-lg-end col-lg-4">{{ Lang::get('redminportal::forms.amount') }}</label>
                                <div class="col-lg-8">
                                    <input type="text" name="amount" value="{{ old('amount', $coupon->amount) }}" class="form-control">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="is_percent" class="col-form-label text-lg-end col-lg-4">{{ Lang::get('redminportal::forms.is_percent') }}</label>
                                <div class="col-lg-8">
                                    <div class="form-check">
                                        <label class="form-check-label">
                                            <input type="checkbox" name="is_percent" value="1" id="is_percent" class="form-check-input" @checked(session()->hasOldInput() ? old('is_percent') : $coupon->is_percent)>
                                            <i>{{ Lang::get('redminportal::forms.discount_explanation') }}</i>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label class="col-form-label text-lg-end col-lg-4">{{ Lang::get('redminportal::forms.start_date') }}</label>
                                <div class="col-lg-8">
                                    <div class="input-group" id="start-date" data-td-target-input="nearest" data-td-target-toggle="nearest">
                                        <input type="text" name="start_date" value="{{ old('start_date', date("d/m/Y h:i A", strtotime($coupon->start_date))) }}" class="form-control" data-td-target="#start-date" autocomplete="off">
                                        <span class="input-group-text" data-td-target="#start-date" data-td-toggle="datetimepicker"><i class="fa-solid fa-calendar"></i></span>
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label class="col-form-label text-lg-end col-lg-4">{{ Lang::get('redminportal::forms.expiry_date') }}</label>
                                <div class="col-lg-8">
                                    <div class="input-group" id="end-date" data-td-target-input="nearest" data-td-target-toggle="nearest">
                                        <input type="text" name="end_date" value="{{ old('end_date', date("d/m/Y h:i A", strtotime($coupon->end_date))) }}" class="form-control" data-td-target="#end-date" autocomplete="off">
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
                                            <input type="checkbox" name="multiple_coupons" value="1" id="multiple_coupons" class="form-check-input" @checked(session()->hasOldInput() ? old('multiple_coupons') : $coupon->multiple_coupons)>
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
                                            <input type="checkbox" name="exclude_sale_item" value="1" id="exclude_sale_item" class="form-check-input" @checked(session()->hasOldInput() ? old('exclude_sale_item') : $coupon->exclude_sale_item)>
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
                                            <input type="checkbox" name="automatically_apply" value="1" id="automatically_apply" class="form-check-input" @checked(session()->hasOldInput() ? old('automatically_apply') : $coupon->automatically_apply)>
                                            <i>{{ Lang::get('redminportal::forms.automatically_apply_explanation') }}</i>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label class="col-form-label text-lg-end col-lg-4">{{ Lang::get('redminportal::forms.min_spent') }}</label>
                                <div class="col-lg-8">
                                    <input type="text" name="min_spent" value="{{ old('min_spent', $coupon->min_spent) }}" class="form-control">
                                    <p class="form-text">{{ Lang::get('redminportal::messages.applies_to_whole_cart_spent') }}</p>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label class="col-form-label text-lg-end col-lg-4">{{ Lang::get('redminportal::forms.max_spent') }}</label>
                                <div class="col-lg-8">
                                    <input type="text" name="max_spent" value="{{ old('max_spent', $coupon->max_spent) }}" class="form-control">
                                    <p class="form-text">{{ Lang::get('redminportal::messages.applies_to_whole_cart_spent') }}</p>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label class="col-form-label text-lg-end col-lg-4">{{ Lang::get('redminportal::forms.restricted_to') }}</label>
                                <div class="col-lg-8">
                                    <h4>{{ Lang::get('redminportal::forms.categories') }}</h4>
                                    @if (count($categories) > 0)
                                    <p></p>
                                    <select name="category_id[]" id="category_id" class="form-select" multiple>
                                        @foreach ($categories as $k => $label)
                                        <option value="{{ $k }}" @selected(in_array((string) $k, array_map('strval', (array) old('category_id', $category_id)), true))>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    @else
                                    <div class="alert alert-warning">{{ Lang::get('redminportal::messages.no_category_found') }}</div>
                                    @endif
                                    <h4>{{ Lang::get('redminportal::forms.products') }}</h4>
                                    @if (count($products) > 0)
                                    <p></p>
                                    <select name="product_id[]" id="product_id" class="form-select" multiple>
                                        @foreach ($products as $k => $label)
                                        <option value="{{ $k }}" @selected(in_array((string) $k, array_map('strval', (array) old('product_id', $product_id)), true))>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    @else
                                    <div class="alert alert-warning">{{ Lang::get('redminportal::messages.no_product_found') }}</div>
                                    @endif
                                    <h4>{{ Lang::get('redminportal::forms.membership_modules') }}</h4>
                                    @if (count($membermodules) > 0)
                                    <p></p>
                                    <select name="pricelist_id[]" id="pricelist_id" class="form-select" multiple>
                                        @foreach ($membermodules as $k => $label)
                                        <option value="{{ $k }}" @selected(in_array((string) $k, array_map('strval', (array) old('pricelist_id', $pricelist_id)), true))>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    @else
                                    <div class="alert alert-warning">{{ Lang::get('redminportal::messages.no_pricelist_found') }}</div>
                                    @endif
                                    <h4>{{ Lang::get('redminportal::forms.bundles') }}</h4>
                                    @if (count($bundles) > 0)
                                    <p></p>
                                    <select name="bundle_id[]" id="bundle_id" class="form-select" multiple>
                                        @foreach ($bundles as $k => $label)
                                        <option value="{{ $k }}" @selected(in_array((string) $k, array_map('strval', (array) old('bundle_id', $bundle_id)), true))>{{ $label }}</option>
                                        @endforeach
                                    </select>
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
                                    <input type="text" name="usage_limit_per_coupon" value="{{ old('usage_limit_per_coupon', $coupon->usage_limit_per_coupon) }}" class="form-control">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label class="col-form-label text-lg-end col-lg-4">{{ Lang::get('redminportal::forms.usage_limit_per_user') }}</label>
                                <div class="col-lg-8">
                                    <input type="text" name="usage_limit_per_user" value="{{ old('usage_limit_per_user', $coupon->usage_limit_per_user) }}" class="form-control">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
	        </div>
            <div class="col-lg-3">
                <div class="card card-body bg-body-tertiary">
                    <div class='form-actions'>
                        <a href="{{ URL::to('admin/coupons') }}" class="btn btn-link btn-sm">{{ Lang::get('redminportal::buttons.cancel') }}</a>
                        <input type="submit" value="{{ Lang::get('redminportal::buttons.save') }}" class="btn btn-primary btn-sm float-end">
                    </div>
                </div>
	        </div>
        </div>
    </form>
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
