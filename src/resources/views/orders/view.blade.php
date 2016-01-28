@extends('redminportal::layouts.master')

@section('navbar-breadcrumb')
    <li class="active"><span class="navbar-text">{{ Lang::get('redminportal::menus.orders') }}</span></li>
@stop

@section('content')

    @include('redminportal::partials.errors')
    
    <div class="row">
        <div class="col-md-8">
            {!! Redminportal::form()->searchForm(url('admin/orders'), url('admin/orders/search'), $searchable_fields, (isset($field) ? $field : null), (isset($search) ? $search : null)) !!}
        </div>
        <div class="col-md-4">
            <div class="nav-controls text-right">
                <div class="btn-group" role="group">
                @if (count($models) > 0)
                <a href="" class="btn btn-default btn-sm disabled btn-text">{{ $models->firstItem() . ' to ' . $models->lastItem() . ' of ' . $models->total() }}</a>
                @endif
                <button class="btn btn-default btn-sm" data-toggle="modal" data-target="#export-csv">{{ Lang::get('redminportal::buttons.export_excel') }}</button>
                {!! HTML::link('admin/orders/create', Lang::get('redminportal::buttons.create_new'), array('class' => 'btn btn-primary btn-sm')) !!}
            </div>
            </div>
        </div>
    </div>

    @if (count($models) > 0)
        <table class="table table-bordered table-striped table-condensed">
            <thead>
                <tr>
                    <th>
                        {!! Redminportal::html()->sorter('admin/orders', 'first_name', $sortBy, $orderBy) !!}
                    </th>
                    <th>
                        {!! Redminportal::html()->sorter('admin/orders', 'last_name', $sortBy, $orderBy) !!}
                    </th>
                    <th>
                        {!! Redminportal::html()->sorter('admin/orders', 'email', $sortBy, $orderBy) !!}
                    </th>
                    <th>{{ Lang::get('redminportal::forms.total_price') }}</th>
                    <th>{{ Lang::get('redminportal::forms.total_discount') }}</th>
                    <th>{{ Lang::get('redminportal::forms.paid') }}</th>
                    <th>
                        {!! Redminportal::html()->sorter('admin/orders', 'payment_status', $sortBy, $orderBy) !!}
                    </th>
                    <th>
                        {!! Redminportal::html()->sorter('admin/orders', 'transaction_id', $sortBy, $orderBy) !!}
                    </th>
                    <th>
                        {!! Redminportal::html()->sorter('admin/orders', 'created_at', $sortBy, $orderBy, trans('redminportal::forms.ordered_on')) !!}
                    </th>
                    <th>{{ Lang::get('redminportal::forms.coupons') }}</th>
                    <th>{{ Lang::get('redminportal::forms.items') }}</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($models as $order)
                <tr>
                    @if ($order->user != null)
                    <td>{{ $order->user->first_name }}</td>
                    <td>{{ $order->user->last_name }}</td>
                    <td>{{ $order->user->email }}</td>
                    @else
                    <td>User deleted</td>
                    <td>User deleted</td>
                    <td>User deleted</td>
                    @endif
                    <td>{{ \Redooor\Redminportal\App\Helpers\RHelper::formatCurrency($order->getTotalprice(), Lang::get('redminportal::currency.currency')) }}</td>
                    <td class="table-actions text-center">
                        @if (count($order->getDiscounts()) > 0)
                        <div class="btn-group">
                            <button type="button" class="btn btn-link dropdown-toggle" data-toggle="dropdown">
								{{ \Redooor\Redminportal\App\Helpers\RHelper::formatCurrency($order->getTotaldiscount(), Lang::get('redminportal::currency.currency')) }}
							</button>
							<ul class="dropdown-menu" role="menu">
                                @foreach ($order->getDiscounts() as $discount)
								<li>
									<a href="#">{{ $discount['name'] }}<br><span class="label label-primary">{{ \Redooor\Redminportal\App\Helpers\RHelper::formatCurrency($discount['value'], Lang::get('redminportal::currency.currency')) }}</span></a>
								</li>
                                @endforeach
							</ul>
						</div>
                        @endif
					</td>
                    <td>{{ \Redooor\Redminportal\App\Helpers\RHelper::formatCurrency($order->paid, Lang::get('redminportal::currency.currency')) }}</td>
                    <td>{{ $order->payment_status }}</td>
                    <td>{{ $order->transaction_id }}</td>
                    <td>{{ date("d/m/y h:i A", strtotime($order->created_at)) }}</td>
                    <td class="table-actions text-center">
                        @if ($order->coupons()->count() > 0)
                        <div class="btn-group">
                            <button type="button" class="btn btn-link dropdown-toggle" data-toggle="dropdown">
								<span class="fa fa-ticket"></span>
							</button>
							<ul class="dropdown-menu pull-right" role="menu">
                                @foreach ($order->coupons as $coupon)
								<li>
									<a href="#">{{ $coupon->code }}<br><span class="label label-primary">{{ $coupon->amount }} @if($coupon->is_percent){{ "%" }}@else{{ "(fixed)" }}@endif</span></a>
								</li>
                                @endforeach
							</ul>
						</div>
                        @endif
					</td>
                    <td class="table-actions text-center">
                        @if ($order->products()->count() > 0 or $order->bundles()->count() > 0 or $order->pricelists()->count())
                        <div class="btn-group">
                            <button type="button" class="btn btn-link dropdown-toggle" data-toggle="dropdown">
								<span class="glyphicon glyphicon-shopping-cart"></span>
							</button>
							<ul class="dropdown-menu pull-right" role="menu">
                                @foreach ($order->products as $product)
								<li>
									<a href="#">{{ $product->name }}<br><span class="label label-primary">{{ $product->sku }}</span></a>
								</li>
                                @endforeach
                                @foreach ($order->bundles as $bundle)
								<li>
                                    <a href="#">{{ $bundle->name }}<br><span class="label label-primary">{{ $bundle->sku }}</span></a>
								</li>
                                @endforeach
                                @foreach ($order->pricelists as $pricelist)
								<li>
                                    <a href="#">{{ $pricelist->name }}<br><span class="label label-primary">{{ $pricelist->module->sku }}</span></a>
								</li>
                                @endforeach
							</ul>
						</div>
                        @endif
					</td>
                    <td class="table-actions text-right">
                        <div class="btn-group">
                            <button type="button" class="btn btn-link dropdown-toggle" data-toggle="dropdown">
								<span class="glyphicon glyphicon-option-horizontal"></span>
							</button>
							<ul class="dropdown-menu pull-right" role="menu">
								<li>
									<a href="{{ URL::to('admin/orders/delete/' . $order->id) }}" class="btn-confirm">
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
        {!! $models->render() !!}
        </div>
    @else
        @if ($models->lastPage())
        <div class="alert alert-info">{{ Lang::get('redminportal::messages.no_record_page_empty') }}</div>
        <a href="{{ $models->url($models->lastPage()) }}" class="btn btn-default"><span class="glyphicon glyphicon-menu-left"></span> {{ Lang::get('redminportal::buttons.previous_page') }}</a>
        @else
        <div class="alert alert-info">{{ Lang::get('redminportal::messages.no_order_found') }}</div>
        @endif
    @endif
    <div id="export-csv" class="modal fade">
        <div class="modal-dialog">
            <div class="modal-content">
                {!! Form::open(array('action' => '\Redooor\Redminportal\App\Http\Controllers\ReportController@postOrders', 'report' => 'form')) !!}
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">{{ Lang::get('redminportal::buttons.close') }}</span></button>
                    <h4 class="modal-title">{{ Lang::get('redminportal::messages.export_to_excel') }}</h4>
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
                    {!! Form::submit(Lang::get('redminportal::buttons.download_excel'), array('class' => 'btn btn-primary')) !!}
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
