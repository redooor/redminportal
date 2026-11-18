@extends('redminportal::layouts.master')

@section('navbar-breadcrumb')
    <li class="nav-item"><a class="nav-link" href="{{ URL::to('admin/purchases') }}">{{ Lang::get('redminportal::menus.purchases') }}</a></li>
    <li class="nav-item"><span class="nav-link active">{{ Lang::get('redminportal::forms.create') }}</span></li>
@stop

@section('content')
    
    @include('redminportal::partials.errors')

    <form method="POST" action="{{ action('\Redooor\Redminportal\App\Http\Controllers\PurchaseController@postStore') }}" enctype="multipart/form-data" role="form">
    @csrf
    
    <div class="row">
        <div class="col-lg-9">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">{{ Lang::get('redminportal::forms.create_purchase') }}</h4>
                </div>
                <div class="card-body">
                    {!! Redminportal::form()->emailInputer() !!}
                    
                    <div class="mb-3">
                        <label for="pricelist_id">{{ Lang::get('redminportal::forms.module_membership') }}</label>
                        <select name="pricelist_id" id="pricelist_id" class="form-select">@foreach ($pricelists_select as $k => $label)<option value="{{ $k }}" @selected((string) $k === (string) old('pricelist_id', null))>{{ $label }}</option>@endforeach</select>
                    </div>
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
        </div>
        <div class="col-lg-3">
            <div class="card card-body bg-body-tertiary">
                <div class='form-actions'>
                    <a href="{{ URL::to('admin/purchases') }}" class="btn btn-link btn-sm">{{ Lang::get('redminportal::buttons.cancel') }}</a>
                    <input type="submit" value="{{ Lang::get('redminportal::buttons.create') }}" class="btn btn-primary btn-sm float-end">
                </div>
            </div>
        </div>
    </div>
    </form>
@stop
