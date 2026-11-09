@extends('redminportal::layouts.master')

@section('navbar-breadcrumb')
    <li class="nav-item"><span class="nav-link active">{{ Lang::get('redminportal::menus.orders') }}</span></li>
@stop

@section('content')

    @include('redminportal::partials.errors')
    
    <div class="row">
        <div class="col-lg-8">
            {!! Redminportal::form()->searchForm(url('admin/orders'), url('admin/orders/search'), $searchable_fields, (isset($field) ? $field : null), (isset($search) ? $search : null)) !!}
        </div>
        <div class="col-lg-4">
            <div class="nav-controls text-end">
                <div class="btn-group" role="group">
                    @if (count($models) > 0)
                        <a href="" class="btn btn-secondary btn-sm disabled btn-text">{{ trans('redminportal::messages.list_from_to', ['firstItem' => $models->firstItem(), 'lastItem' => $models->lastItem(), 'totalItem' => $models->total()]) }}</a>
                    @endif
                    <button class="btn btn-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#export-csv">{{ Lang::get('redminportal::buttons.export_excel') }}</button>
                    <a href="{{ url('admin/orders/create') }}" class="btn btn-primary btn-sm">{{ Lang::get('redminportal::buttons.create_new') }}</a>
                </div>
            </div>
        </div>
    </div>

    @if (count($models) > 0)
        <div class="table-responsive">
        <table class="table table-striped table-sm">
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
                            <button type="button" class="btn btn-link dropdown-toggle" data-bs-toggle="dropdown" data-bs-popper-config='{"strategy":"fixed"}'>
								{{ \Redooor\Redminportal\App\Helpers\RHelper::formatCurrency($order->getTotaldiscount(), Lang::get('redminportal::currency.currency')) }}
							</button>
							<ul class="dropdown-menu">
                                @foreach ($order->getDiscounts() as $discount)
								<li>
									<a class="dropdown-item" href="#">{{ $discount['name'] }}<br><span class="badge text-bg-primary">{{ \Redooor\Redminportal\App\Helpers\RHelper::formatCurrency($discount['value'], Lang::get('redminportal::currency.currency')) }}</span></a>
								</li>
                                @endforeach
							</ul>
						</div>
                        @endif
					</td>
                    <td>{{ \Redooor\Redminportal\App\Helpers\RHelper::formatCurrency($order->paid, Lang::get('redminportal::currency.currency')) }}</td>
                    <td class="table-actions text-center">
                        <div class="btn-group">
                            <button type="button" class="btn btn-link dropdown-toggle" data-bs-toggle="dropdown" data-bs-popper-config='{"strategy":"fixed"}'>
								{{ $order->payment_status }}
							</button>
                            <ul class="dropdown-menu">
                                <li class="dropdown-header">Change Status</li>
                                @foreach ($payment_statuses as $key => $value)
                                <li><a class="dropdown-item" href="{{ url('admin/orders/update/status/' . $order->id . '/' . $key) }}">{{ $value }}</a></li>
                                @endforeach
                            </ul>
                        </div>
                    </td>
                    <td>{{ $order->transaction_id }}</td>
                    <td>{{ date("d/m/y h:i A", strtotime($order->created_at)) }}</td>
                    <td class="table-actions text-center">
                        @if ($order->coupons()->count() > 0)
                        <div class="btn-group">
                            <button type="button" class="btn btn-link dropdown-toggle" data-bs-toggle="dropdown" data-bs-popper-config='{"strategy":"fixed"}'>
								<i class="fa-solid fa-ticket"></i>
							</button>
							<ul class="dropdown-menu dropdown-menu-end">
                                @foreach ($order->coupons as $coupon)
								<li>
									<a class="dropdown-item" href="#">{{ $coupon->code }}<br><span class="badge text-bg-primary">{{ $coupon->amount }} @if($coupon->is_percent){{ "%" }}@else{{ "(fixed)" }}@endif</span></a>
								</li>
                                @endforeach
							</ul>
						</div>
                        @endif
					</td>
                    <td class="table-actions text-center">
                        @if ($order->products()->count() > 0 || $order->bundles()->count() > 0 || $order->pricelists()->count())
                        <div class="btn-group">
                            <button type="button" class="btn btn-link dropdown-toggle" data-bs-toggle="dropdown" data-bs-popper-config='{"strategy":"fixed"}'>
								<i class="fa-solid fa-cart-shopping"></i>
							</button>
							<ul class="dropdown-menu dropdown-menu-end">
                                @foreach ($order->products()->get() as $product)
								<li>
									<a class="dropdown-item" href="#">{{ $product->name }}<br>
                                        <span class="badge text-bg-primary">{{ $product->sku }}</span><br>
                                        <span class="badge text-bg-success">Qty: {{ $order->products()->where('name', $product->name)->count() }}</span>
                                    </a>
								</li>
                                @endforeach
                                @foreach ($order->bundles()->get() as $bundle)
								<li>
                                    <a class="dropdown-item" href="#">{{ $bundle->name }}<br>
                                        <span class="badge text-bg-primary">{{ $bundle->sku }}</span><br>
                                        <span class="badge text-bg-success">Qty: {{ $order->bundles()->where('name', $bundle->name)->count() }}</span>
                                    </a>
								</li>
                                @endforeach
                                @foreach ($order->pricelists as $pricelist)
								<li>
                                    <a class="dropdown-item" href="#">{{ $pricelist->name }}<br><span class="badge text-bg-primary">{{ $pricelist->module->sku }}</span></a>
								</li>
                                @endforeach
							</ul>
						</div>
                        @endif
					</td>
                    <td class="table-actions text-end">
                        <div class="btn-group">
                            <button type="button" class="btn btn-link dropdown-toggle" data-bs-toggle="dropdown" data-bs-popper-config='{"strategy":"fixed"}'>
								<i class="fa-solid fa-ellipsis"></i>
							</button>
							<ul class="dropdown-menu dropdown-menu-end">
                                <li>
									<a class="dropdown-item btn-modal-revision" href="{{ URL::to('admin/orders/revisions/' . $order->id) }}">
										<i class="fa-solid fa-clock"></i>Revision History</a>
								</li>
								<li>
									<a class="dropdown-item btn-confirm" href="{{ URL::to('admin/orders/delete/' . $order->id) }}">
										<i class="fa-solid fa-xmark"></i>{{ Lang::get('redminportal::buttons.delete') }}</a>
								</li>
							</ul>
						</div>
					</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        </div>
        <div class="text-center">
        {!! $models->render('pagination::bootstrap-5') !!}
        </div>
    @else
        @if ($models->total() > 0)
        <div class="p-3 rounded bg-body-secondary text-body-secondary">{{ Lang::get('redminportal::messages.no_record_page_empty') }}</div>
        <a href="{{ $models->url($models->lastPage()) }}" class="btn btn-secondary"><i class="fa-solid fa-chevron-left"></i> {{ Lang::get('redminportal::buttons.previous_page') }}</a>
        @else
        <div class="p-3 rounded bg-body-secondary text-body-secondary">{{ Lang::get('redminportal::messages.no_order_found') }}</div>
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
                $( '.input-group-text' ).click( function() {
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
                    // jQuery 3 removed the .load(handler) shorthand: bind the event before setting src
                    $('#iframe-revision-history').removeAttr('src').empty().data('loaded', false).off('load').on('load', function() {
                        $(this).data('loaded', true);
                        $('#revision-modal-progress').parent().fadeOut();
                    }).attr('src', $create_url);
                    $('#revision-modal-window').modal('show');
                });
                $('#revision-modal-window').on('shown.bs.modal', function (e) {
                    // The iframe may already have loaded while the modal was fading in
                    if ($('#iframe-revision-history').data('loaded')) { return; }
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
