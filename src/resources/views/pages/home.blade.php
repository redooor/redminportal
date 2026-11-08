@extends('redminportal::layouts.master')

@section('content')
    <div class="row g-3 mb-3">
        @if ($can['orders'])
            <div class="col-sm-6 col-xl-3">
                <div class="card h-100">
                    <div class="card-body">
                        <p class="text-body-secondary mb-1">{{ trans('redminportal::dashboard.orders') }}</p>
                        <p class="display-6 mb-0">{{ $stats['order_totals']['orders'] }}</p>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card h-100">
                    <div class="card-body">
                        <p class="text-body-secondary mb-1">{{ trans('redminportal::dashboard.revenue') }}</p>
                        <p class="display-6 mb-0">{{ number_format($stats['order_totals']['revenue'], 2) }}</p>
                    </div>
                </div>
            </div>
        @endif
        @if ($can['users'])
            <div class="col-sm-6 col-xl-3">
                <div class="card h-100">
                    <div class="card-body">
                        <p class="text-body-secondary mb-1">{{ trans('redminportal::dashboard.new_users') }}</p>
                        <p class="display-6 mb-0">{{ $stats['new_users'] }}</p>
                    </div>
                </div>
            </div>
        @endif
        @if ($can['products'])
            <div class="col-sm-6 col-xl-3">
                <div class="card h-100">
                    <div class="card-body">
                        <p class="text-body-secondary mb-1">{{ trans('redminportal::dashboard.active_products') }}</p>
                        <p class="display-6 mb-0">{{ $stats['active_products'] }}</p>
                    </div>
                </div>
            </div>
        @endif
    </div>

    @if ($can['orders'])
        <div class="row g-3 mb-3">
            <div class="col-lg-8">
                <div class="card h-100">
                    <div class="card-body">
                        <h2 class="h5 card-title">{{ trans('redminportal::dashboard.daily_chart') }}</h2>
                        <div class="chart-wrapper">
                            <canvas id="chart-daily" role="img" aria-label="{{ trans('redminportal::dashboard.daily_chart_aria') }}"></canvas>
                        </div>
                        <p class="text-body-secondary d-none" data-empty-state>{{ trans('redminportal::dashboard.no_chart_data') }}</p>
                        <p class="small text-body-secondary mt-2 mb-0" data-revenue-note>{{ trans('redminportal::dashboard.revenue_note', ['statuses' => implode(' and ', \Redooor\Redminportal\App\Classes\DashboardStats::REVENUE_STATUSES)]) }}</p>
                        <table class="visually-hidden">
                            <caption>{{ trans('redminportal::dashboard.daily_chart') }}</caption>
                            <thead>
                                <tr>
                                    <th>{{ trans('redminportal::dashboard.date') }}</th>
                                    <th>{{ trans('redminportal::dashboard.orders') }}</th>
                                    <th>{{ trans('redminportal::dashboard.revenue') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($stats['daily']['labels'] as $index => $label)
                                    <tr>
                                        <td>{{ $label }}</td>
                                        <td>{{ $stats['daily']['orders'][$index] }}</td>
                                        <td>{{ number_format($stats['daily']['revenue'][$index], 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h2 class="h5 card-title">{{ trans('redminportal::dashboard.status_chart') }}</h2>
                        <div class="chart-wrapper">
                            <canvas id="chart-status" role="img" aria-label="{{ trans('redminportal::dashboard.status_chart_aria') }}"></canvas>
                        </div>
                        <p class="text-body-secondary d-none" data-empty-state>{{ trans('redminportal::dashboard.no_chart_data') }}</p>
                        <table class="visually-hidden">
                            <caption>{{ trans('redminportal::dashboard.status_chart') }}</caption>
                            <thead>
                                <tr>
                                    <th>{{ trans('redminportal::dashboard.status') }}</th>
                                    <th>{{ trans('redminportal::dashboard.orders') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($stats['status'] as $status => $count)
                                    <tr>
                                        <td>{{ $status }}</td>
                                        <td>{{ $count }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="row g-3 mb-3">
        @if ($can['orders'] && $can['products'])
            <div class="col-lg-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h2 class="h5 card-title">{{ trans('redminportal::dashboard.top_products') }}</h2>
                        @if (count($stats['top_products']) > 0)
                            <ul class="list-group list-group-flush">
                                @foreach ($stats['top_products'] as $product)
                                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                        <span>{{ $product['name'] }}</span>
                                        <span class="badge text-bg-primary">{{ $product['orders'] }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="text-body-secondary">{{ trans('redminportal::dashboard.no_top_products') }}</p>
                        @endif
                    </div>
                </div>
            </div>
        @endif
        @if ($can['orders'])
            <div class="col-lg-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h2 class="h5 card-title">{{ trans('redminportal::dashboard.recent_orders') }}</h2>
                        @if (count($stats['recent_orders']) > 0)
                            <div class="list-group list-group-flush">
                                @foreach ($stats['recent_orders'] as $order)
                                    <a href="{{ url('admin/orders') }}" class="list-group-item list-group-item-action px-0">
                                        <div class="d-flex w-100 justify-content-between">
                                            <span>#{{ $order->id }}</span>
                                            <small class="text-body-secondary">{{ $order->created_at ? $order->created_at->diffForHumans() : '' }}</small>
                                        </div>
                                        <small>{{ $order->user ? $order->user->email : trans('redminportal::dashboard.unknown_user') }}</small>
                                        <div class="d-flex w-100 justify-content-between">
                                            <small>{{ number_format($order->paid ?? 0, 2) }}</small>
                                            <small>{{ $order->payment_status ?? trans('redminportal::dashboard.unknown_status') }}</small>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        @else
                            <p class="text-body-secondary">{{ trans('redminportal::dashboard.no_recent_orders') }}</p>
                        @endif
                    </div>
                </div>
            </div>
        @endif
        @if ($can['posts'] || $can['pages'])
            <div class="col-lg-4">
                <div class="d-flex flex-column gap-3 h-100">
                @if ($can['posts'])
                    <div class="card flex-fill">
                        <div class="card-body">
                            <h2 class="h5 card-title">{{ trans('redminportal::dashboard.recent_posts') }}</h2>
                            @if (count($stats['recent_posts']) > 0)
                                <div class="list-group list-group-flush">
                                    @foreach ($stats['recent_posts'] as $post)
                                        <a href="{{ url('admin/posts/edit/' . $post->id) }}" class="list-group-item list-group-item-action px-0">
                                            <div class="d-flex w-100 justify-content-between">
                                                <span>{{ $post->title }}</span>
                                                <small class="text-body-secondary">{{ $post->updated_at ? $post->updated_at->diffForHumans() : '' }}</small>
                                            </div>
                                        </a>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-body-secondary">{{ trans('redminportal::dashboard.no_recent_posts') }}</p>
                            @endif
                        </div>
                    </div>
                @endif
                @if ($can['pages'])
                    <div class="card flex-fill">
                        <div class="card-body">
                            <h2 class="h5 card-title">{{ trans('redminportal::dashboard.recent_pages') }}</h2>
                            @if (count($stats['recent_pages']) > 0)
                                <div class="list-group list-group-flush">
                                    @foreach ($stats['recent_pages'] as $page)
                                        <a href="{{ url('admin/pages/edit/' . $page->id) }}" class="list-group-item list-group-item-action px-0">
                                            <div class="d-flex w-100 justify-content-between">
                                                <span>{{ $page->title }}</span>
                                                <small class="text-body-secondary">{{ $page->updated_at ? $page->updated_at->diffForHumans() : '' }}</small>
                                            </div>
                                        </a>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-body-secondary">{{ trans('redminportal::dashboard.no_recent_pages') }}</p>
                            @endif
                        </div>
                    </div>
                @endif
                </div>
            </div>
        @endif
    </div>

    <div class="row g-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h2 class="h5 card-title">{{ trans('redminportal::dashboard.whats_new_heading') }}</h2>
                    <ul class="mb-2">
                        <li>{{ trans('redminportal::dashboard.whats_new_item_1') }}</li>
                        <li>{{ trans('redminportal::dashboard.whats_new_item_2') }}</li>
                        <li>{{ trans('redminportal::dashboard.whats_new_item_3') }}</li>
                    </ul>
                    <p class="mb-0">{!! str_replace(':link', '<a href="https://github.com/redooor/redminportal/blob/master/CHANGELOG.md" target="_blank" rel="noopener">' . e(trans('redminportal::dashboard.whats_new_changelog_link')) . '</a>', e(trans('redminportal::dashboard.whats_new_changelog'))) !!}</p>
                </div>
            </div>
        </div>
    </div>

    @if ($can['orders'])
        <script type="application/json" id="dashboard-data">{!! \Illuminate\Support\Js::encode($payload) !!}</script>
        <script defer src="{{ URL::to('vendor/redooor/redminportal/js/chart/chart.umd.min.js') }}"></script>
        <script defer src="{{ URL::to('vendor/redooor/redminportal/js/redmin-dashboard.min.js') }}"></script>
    @endif
@stop
