@extends('redminportal::layouts.master')

@section('navbar-breadcrumb')
    <li class="nav-item"><a class="nav-link" href="{{ URL::to('admin/purchases') }}">{{ Lang::get('redminportal::menus.purchases') }}</a></li>
    <li class="nav-item"><span class="nav-link active">{{ Lang::get('redminportal::forms.create') }}</span></li>
@stop

@section('content')
    
    @include('redminportal::partials.errors')

    {!! Form::open(array('files' => TRUE, 'action' => '\Redooor\Redminportal\App\Http\Controllers\PurchaseController@postStore', 'role' => 'form')) !!}
    
    <div class="row">
        <div class="col-lg-9">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">{{ Lang::get('redminportal::forms.create_purchase') }}</h4>
                </div>
                <div class="card-body">
                    {!! Redminportal::form()->emailInputer() !!}
                    
                    <div class="mb-3">
                        {!! Form::label('pricelist_id', Lang::get('redminportal::forms.module_membership')) !!}
                        {!! Form::select('pricelist_id', $pricelists_select, null, array('class' => 'form-select')) !!}
                    </div>
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
        </div>
        <div class="col-lg-3">
            <div class="card card-body bg-body-tertiary">
                <div class='form-actions'>
                    {!! HTML::link('admin/purchases', Lang::get('redminportal::buttons.cancel'), array('class' => 'btn btn-link btn-sm'))!!}
                    {!! Form::submit(Lang::get('redminportal::buttons.create'), array('class' => 'btn btn-primary btn-sm float-end')) !!}
                </div>
            </div>
        </div>
    </div>
    {!! Form::close() !!}
@stop
