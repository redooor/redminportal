@extends('redminportal::layouts.master')

@section('navbar-breadcrumb')
    <li><a href="{{ URL::to('admin/purchases') }}">{{ Lang::get('redminportal::menus.purchases') }}</a></li>
    <li class="active"><span class="navbar-text">{{ Lang::get('redminportal::forms.create') }}</span></li>
@stop

@section('content')
    
    @include('redminportal::partials.errors')

    {!! Form::open(array('files' => TRUE, 'action' => '\Redooor\Redminportal\App\Http\Controllers\PurchaseController@postStore', 'role' => 'form')) !!}
    
    <div class="row">
        <div class="col-md-9">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4 class="panel-title">{{ Lang::get('redminportal::forms.create_purchase') }}</h4>
                </div>
                <div class="panel-body">
                    {!! Redminportal::form()->emailInputer() !!}
                    
                    <div class="form-group">
                        {!! Form::label('pricelist_id', Lang::get('redminportal::forms.module_membership')) !!}
                        {!! Form::select('pricelist_id', $pricelists_select, null, array('class' => 'form-control')) !!}
                    </div>
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
        </div>
        <div class="col-md-3">
            <div class="well">
                <div class='form-actions'>
                    {!! HTML::link('admin/purchases', Lang::get('redminportal::buttons.cancel'), array('class' => 'btn btn-link btn-sm'))!!}
                    {!! Form::submit(Lang::get('redminportal::buttons.create'), array('class' => 'btn btn-primary btn-sm pull-right')) !!}
                </div>
            </div>
        </div>
    </div>
    {!! Form::close() !!}
@stop
