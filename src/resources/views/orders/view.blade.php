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
                        <a href="" class="btn btn-default btn-sm disabled btn-text">{{ trans('redminportal::messages.list_from_to', ['firstItem' => $models->firstItem(), 'lastItem' => $models->lastItem(), 'totalItem' => $models->total()]) }}</a>
                    @endif
                    <button class="btn btn-default btn-sm" data-toggle="modal" data-target="#export-csv">{{ Lang::get('redminportal::buttons.export_excel') }}</button>
                    <a href="{{ url('admin/orders/create') }}" class="btn btn-primary btn-sm">{{ Lang::get('redminportal::buttons.create_new') }}</a>
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
                    <td class="table-actions text-center">
                        <div class="btn-group">
                            <button type="button" class="btn btn-link dropdown-toggle" data-toggle="dropdown">
								{{ $order->payment_status }}
							</button>
                            <ul class="dropdown-menu">
                                <li class="dropdown-header">Change Status</li>
                                @foreach ($payment_statuses as $key => $value)
                                <li><a href="{{ url('admin/orders/update/status/' . $order->id . '/' . $key) }}">{{ $value }}</a></li>
                                @endforeach
                            </ul>
                        </div>
                    </td>
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
                                @foreach ($order->products()->groupBy('name')->get() as $product)
								<li>
									<a href="#">{{ $product->name }}<br>
                                        <span class="label label-primary">{{ $product->sku }}</span><br>
                                        <span class="label label-success">Qty: {{ $order->products()->where('name', $product->name)->count() }}</span>
                                    </a>
								</li>
                                @endforeach
                                @foreach ($order->bundles()->groupBy('name')->get() as $bundle)
								<li>
                                    <a href="#">{{ $bundle->name }}<br>
                                        <span class="label label-primary">{{ $bundle->sku }}</span><br>
                                        <span class="label label-success">Qty: {{ $order->bundles()->where('name', $bundle->name)->count() }}</span>
                                    </a>
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
									<a href="{{ URL::to('admin/orders/revisions/' . $order->id) }}" class="btn-modal-revision">
										<i class="glyphicon glyphicon-time"></i>Revision History</a>
								</li>
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
    {{-- Include Export Modal --}}
    @include('redminportal::partials.modal-export', [
        'export_id' => 'export-csv',
        'export_title' => trans('redminportal::messages.export_to_excel'),
        'export_url' => url('admin/reports/orders')
    ])

    {{-- Modal window for Revision table --}}
    {!! Redminportal::html()->modalWindow(
        'revision-modal-window',
        trans('redminportal::forms.revision_history_title'),
        '<iframe id="iframe-revision-history"></iframe>',
        null,
        'modal-lg',
        'revision-modal-progress'
    ) !!}
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
                //---------------------------------------------------------
                // View revision history
                //---------------------------------------------------------
                $(document).on('click', '.btn-modal-revision', function(e) {
                    e.preventDefault();
                    // Make iframe height 70% of window height
                    $window_height = $(window).height();
                    $('#iframe-revision-history').css('height', Math.round(($window_height*0.7-80), 0));
                    // Load the source
                    $create_url = $(this).attr('href');
                    $('#iframe-revision-history').removeAttr('src').empty().attr('src', $create_url).load(function() {
                        $('#revision-modal-progress').parent().fadeOut();
                    });
                    $('#revision-modal-window').modal('show');
                });
                $('#revision-modal-window').on('shown.bs.modal', function (e) {
                    $('#revision-modal-progress').css('width', '0%').parent().fadeIn(function() {
                        for(ipercent = 0; ipercent<=50; ipercent++) {
                            $('#revision-modal-progress').delay(4000).css('width', ipercent*2 + '%');
                        }
                    });
                });
                // Clear content when modal hidden
                $('#revision-modal-window').on('hidden.bs.modal', function (e) {
                    $('#iframe-revision-history').removeAttr('src').empty();
                });
            })
        }(window.jQuery);
    </script>
@stop
