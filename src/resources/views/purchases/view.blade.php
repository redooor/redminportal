@extends('redminportal::layouts.master')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <ol class="breadcrumb">
                <li><a href="{{ URL::to('admin') }}">{{ Lang::get('redminportal::menus.home') }}</a></li>
                <li class="active">{{ Lang::get('redminportal::menus.purchases') }}</li>
            </ol>
        </div>
    </div>

    @include('redminportal::partials.errors')
    
    <div class="row">
        <div class="col-md-12">
            <div class="nav-controls text-right">
                <div class="btn-group" role="group">
                @if (count($purchases) > 0)
                <a href="" class="btn btn-default btn-sm disabled btn-text">{{ $purchases->firstItem() . ' to ' . $purchases->lastItem() . ' of ' . $purchases->total() }}</a>
                @endif
                <button class="btn btn-default btn-sm" data-toggle="modal" data-target="#export-csv">{{ Lang::get('redminportal::buttons.export_csv') }}</button>
                {!! HTML::link('admin/purchases/create', Lang::get('redminportal::buttons.create_new'), array('class' => 'btn btn-primary btn-sm')) !!}
            </div>
            </div>
        </div>
    </div>

    @if (count($purchases) > 0)
        <table class="table table-bordered table-striped table-condensed">
            <thead>
                <tr>
                    <th>User</th>
                    <th>{{ Lang::get('redminportal::forms.email') }}</th>
                    <th>{{ Lang::get('redminportal::forms.module_name') }}</th>
                    <th>Membership</th>
                    <th>Paid</th>
                    <th>Payment Status</th>
                    <th>Transaction ID</th>
                    <th>Purchased on</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($purchases as $purchase)
                <tr>
                    @if ($purchase->user != null)
                    <td>{{ $purchase->user->first_name }} {{ $purchase->user->last_name }}</td>
                    <td>{{ $purchase->user->email }}</td>
                    @else
                    <td>User deleted</td>
                    <td>User deleted</td>
                    @endif
                    <td>{{ $purchase->pricelist->module->name }}</td>
                    <td>{{ $purchase->pricelist->membership->name }}</td>
                    <td>{{ \Redooor\Redminportal\App\Helpers\RHelper::formatCurrency($purchase->paid, Lang::get('redminportal::currency.currency')) }}</td>
                    <td>{{ $purchase->payment_status }}</td>
                    <td>{{ $purchase->transaction_id }}</td>
                    <td>{{ $purchase->created_at }}</td>
                    <td class="table-actions text-right">
                        <div class="btn-group">
                            <button type="button" class="btn btn-link dropdown-toggle" data-toggle="dropdown">
								<span class="glyphicon glyphicon-option-horizontal"></span>
							</button>
							<ul class="dropdown-menu pull-right" role="menu">
								<li>
									<a href="{{ URL::to('admin/purchases/delete/' . $purchase->id) }}" class="btn-confirm">
										<i class="glyphicon glyphicon-remove"></i>{{ Lang::get('redminportal::buttons.delete') }}</a>
								</li>
							</ul>
						</div>
					</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="text-center">
        {!! $purchases->render() !!}
        </div>
    @else
        <div class="alert alert-info">No purchase found</div>
    @endif
    <div id="export-csv" class="modal fade">
        <div class="modal-dialog">
            <div class="modal-content">
                {!! Form::open(array('action' => '\Redooor\Redminportal\App\Http\Controllers\ReportController@postPurchases', 'report' => 'form')) !!}
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">{{ Lang::get('redminportal::buttons.close') }}</span></button>
                    <h4 class="modal-title">{{ Lang::get('redminportal::messages.export_to_csv') }}</h4>
                </div>
                <div class="modal-body">
                    <div class='row'>
                        <div class="col-md-12">
                            <div class="form-group">
                                {!! Form::label('start_date', Lang::get('redminportal::forms.start_date')) !!}
                                <div class="input-group" id='start-date'>
                                    {!! Form::input('text', 'start_date', null, array('class' => 'form-control datepicker', 'readonly')) !!}
                                    <span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>
                                </div>
                            </div>
                            <div class="form-group">
                                {!! Form::label('end_date', Lang::get('redminportal::forms.end_date')) !!}
                                <div class="input-group" id='end-date'>
                                    {!! Form::input('text', 'end_date', null, array('class' => 'form-control datepicker', 'readonly')) !!}
                                    <span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>
                                </div>
                            </div>
                            <p class="help-block">{{ Lang::get('redminportal::messages.leave_all_blank_to_download_all') }}</p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">{{ Lang::get('redminportal::buttons.close') }}</button>
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
                $( '.input-group-addon' ).click( function() {
                    $( this ).parent().find('input').datepicker( "show" );
                });
            })
        }(window.jQuery);
    </script>
@stop
