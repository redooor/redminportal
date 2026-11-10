@extends('redminportal::layouts.master')

@section('navbar-breadcrumb')
    <li class="nav-item"><span class="nav-link active">{{ Lang::get('redminportal::menus.purchases') }}</span></li>
@stop

@section('content')

    @include('redminportal::partials.errors')
    
    <div class="row">
        <div class="col-lg-12">
            <div class="alert alert-warning">
                This page is no longer in use. Please go to <a href="{{ URL::to('admin/orders') }}" class="btn btn-secondary btn-sm">Orders</a> instead.
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12">
            <div class="nav-controls text-end">
                <div class="btn-group" role="group">
                @if (count($purchases) > 0)
                <a href="" class="btn btn-secondary btn-sm disabled btn-text">{{ $purchases->firstItem() . ' to ' . $purchases->lastItem() . ' of ' . $purchases->total() }}</a>
                @endif
                <button class="btn btn-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#export-csv">{{ Lang::get('redminportal::buttons.export_csv') }}</button>
                {!! HTML::link('admin/purchases/create', Lang::get('redminportal::buttons.create_new'), array('class' => 'btn btn-primary btn-sm')) !!}
            </div>
            </div>
        </div>
    </div>

    @if (count($purchases) > 0)
        <div class="table-responsive">
        <table class="table table-striped table-sm">
            <thead>
                <tr>
                    <th>User</th>
                    <th>{{ Lang::get('redminportal::forms.email') }}</th>
                    <th>{{ Lang::get('redminportal::forms.module_name') }}</th>
                    <th>{{ Lang::get('redminportal::forms.membership') }}</th>
                    <th>{{ Lang::get('redminportal::forms.paid') }}</th>
                    <th>{{ Lang::get('redminportal::forms.payment_status') }}</th>
                    <th>{{ Lang::get('redminportal::forms.transaction_id') }}</th>
                    <th>{{ Lang::get('redminportal::forms.purchased_on') }}</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($purchases as $purchase)
                    @if (isset($purchase->pricelist))
                    <tr>
                        @if ($purchase->user != null)
                        <td>{{ $purchase->user->first_name }} {{ $purchase->user->last_name }}</td>
                        <td>{{ $purchase->user->email }}</td>
                        @else
                        <td>User deleted</td>
                        <td>User deleted</td>
                        @endif
                        <td>{{ $purchase->pricelist->module->name ?? '' }}</td>
                        <td>{{ $purchase->pricelist->membership->name ?? '' }}</td>
                        <td>{{ \Redooor\Redminportal\App\Helpers\RHelper::formatCurrency($purchase->paid, Lang::get('redminportal::currency.currency')) }}</td>
                        <td>{{ $purchase->payment_status }}</td>
                        <td>{{ $purchase->transaction_id }}</td>
                        <td>{{ $purchase->created_at }}</td>
                        <td class="table-actions text-end">
                            <div class="btn-group">
                                <button type="button" class="btn btn-link dropdown-toggle" data-bs-toggle="dropdown" data-bs-popper-config='{"strategy":"fixed"}'>
                                    <i class="fa-solid fa-ellipsis"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <a class="dropdown-item btn-confirm" href="{{ URL::to('admin/purchases/delete/' . $purchase->id) }}">
                                            <i class="fa-solid fa-xmark"></i>{{ Lang::get('redminportal::buttons.delete') }}</a>
                                    </li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
        </div>
        <div class="text-center">
        {!! $purchases->render('pagination::bootstrap-5') !!}
        </div>
    @else
        @if ($purchases->total() > 0)
        <div class="p-3 rounded bg-body-secondary text-body-secondary">{{ Lang::get('redminportal::messages.no_record_page_empty') }}</div>
        <a href="{{ $purchases->url($purchases->lastPage()) }}" class="btn btn-secondary"><i class="fa-solid fa-chevron-left"></i> {{ Lang::get('redminportal::buttons.previous_page') }}</a>
        @else
        <div class="p-3 rounded bg-body-secondary text-body-secondary">{{ Lang::get('redminportal::messages.no_purchase_found') }}</div>
        @endif
    @endif
    <div id="export-csv" class="modal fade">
        <div class="modal-dialog">
            <div class="modal-content">
                {!! Form::open(array('action' => '\Redooor\Redminportal\App\Http\Controllers\ReportController@postPurchases', 'report' => 'form')) !!}
                <div class="modal-header">
                    <h4 class="modal-title">{{ Lang::get('redminportal::messages.export_to_csv') }}</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class='row'>
                        <div class="col-lg-12">
                            <div class="mb-3">
                                {!! Form::label('start_date', Lang::get('redminportal::forms.start_date')) !!}
                                <div class="input-group" id='start-date'>
                                    {!! Form::input('text', 'start_date', null, array('class' => 'form-control datepicker', 'readonly')) !!}
                                    <span class="input-group-text"><i class="fa-solid fa-calendar"></i></span>
                                </div>
                            </div>
                            <div class="mb-3">
                                {!! Form::label('end_date', Lang::get('redminportal::forms.end_date')) !!}
                                <div class="input-group" id='end-date'>
                                    {!! Form::input('text', 'end_date', null, array('class' => 'form-control datepicker', 'readonly')) !!}
                                    <span class="input-group-text"><i class="fa-solid fa-calendar"></i></span>
                                </div>
                            </div>
                            <p class="form-text">{{ Lang::get('redminportal::messages.leave_all_blank_to_download_all') }}</p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ Lang::get('redminportal::buttons.close') }}</button>
                    {!! Form::submit(Lang::get('redminportal::buttons.download_csv'), array('class' => 'btn btn-primary')) !!}
                </div>
                {!! Form::close() !!}
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->
@stop

@section('footer')
    <script>
        !function ($) {
            $(function(){
                // For datepicker
                $( '.datepicker' ).datepicker({ dateFormat: 'dd/mm/yy' });
                $('.datepicker').css('z-index', '1051'); // make picker on top of modal window
                $( '.input-group-text' ).click( function() {
                    $( this ).parent().find('input').datepicker( "show" );
                });
            })
        }(window.jQuery);
    </script>
@stop
