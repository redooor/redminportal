@extends('redminportal::layouts.master')

@section('content')
    @if (isset($errors))
        @if($errors->has())
        <div class='alert alert-danger'>
            We encountered the following errors:
            <ul>
                @foreach($errors->all() as $message)
                <li>{{ $message }}</li>
                @endforeach
            </ul>
        </div>
        @endif
    @endif

    <div class="nav-controls text-right">
        @if (count($purchases) > 0)
        <span class="label label-default pull-left">
            {{ $purchases->firstItem() . ' to ' . $purchases->lastItem() . ' ( total ' . $purchases->total() . ' )' }}
        </span>
        @endif
        <button class="btn btn-default" data-toggle="modal" data-target="#export-csv">Export CSV</button>
        {!! HTML::link('admin/purchases/create', Lang::get('redminportal::buttons.create_new'), array('class' => 'btn btn-primary')) !!}
    </div>

    @if (count($purchases) > 0)
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Email</th>
                    <th>Module Name</th>
                    <th>Membership</th>
                    <th>Paid</th>
                    <th>Payment Status</th>
                    <th>Transaction ID</th>
                    <th>Purchased on</th>
                    <th>Action</th>
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
                    <td>
						<div class="btn-group">
							<button type="button" class="btn btn-default btn-xs dropdown-toggle" data-toggle="dropdown">
								Action <span class="caret"></span>
							</button>
							<ul class="dropdown-menu pull-right" role="menu">
								<li>
									<a href="{{ URL::to('admin/purchases/delete/' . $purchase->id) }}" class="btn-confirm">
										<i class="glyphicon glyphicon-remove"></i>Delete</a>
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
                    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                    <h4 class="modal-title">Export to CSV</h4>
                </div>
                <div class="modal-body">
                    <div class='row'>
                        <div class="col-md-12">
                            <div class="form-group">
                                {!! Form::label('start_date', 'Start Date') !!}
                                <div class="input-group" id='start-date'>
                                    {!! Form::input('text', 'start_date', null, array('class' => 'form-control datepicker', 'readonly')) !!}
                                    <span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>
                                </div>
                            </div>
                            <div class="form-group">
                                {!! Form::label('end_date', 'End Date') !!}
                                <div class="input-group" id='end-date'>
                                    {!! Form::input('text', 'end_date', null, array('class' => 'form-control datepicker', 'readonly')) !!}
                                    <span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>
                                </div>
                            </div>
                            <p class="help-block">Leave the dates empty to download all.</p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    {!! Form::submit('Download CSV', array('class' => 'btn btn-primary')) !!}
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
