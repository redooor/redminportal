/*
 * Redmin dashboard charts: orders/revenue over the last 30 days and orders by status.
 *
 * Markup:
 *   <script type="application/json" id="dashboard-data">{"daily": {...}, "status": {...}, "names": {"orders": "...", "revenue": "..."}}</script>
 *   <div class="card">
 *     <div class="chart-wrapper"><canvas id="chart-daily"></canvas></div>
 *     <p class="d-none" data-empty-state>...</p>
 *   </div>
 *   <div class="card">
 *     <div class="chart-wrapper"><canvas id="chart-status"></canvas></div>
 *     <p class="d-none" data-empty-state>...</p>
 *   </div>
 *
 * Colours are read from the Bootstrap CSS custom properties whenever the
 * charts are drawn, so a change of data-bs-theme repaints them.
 */
(function () {
    'use strict';

    var DATA_ID = 'dashboard-data';
    var DAILY_ID = 'chart-daily';
    var STATUS_ID = 'chart-status';
    var THEME_ATTRIBUTE = 'data-bs-theme';
    // Known payment statuses keep a meaningful colour; any other status takes the next colour from the fallback list.
    var STATUS_COLOURS = {
        'Completed': '--bs-success',
        'Delivered': '--bs-primary',
        'In Progress': '--bs-info',
        'Pending': '--bs-warning',
        'Canceled': '--bs-danger',
        'Refunded': '--bs-secondary',
        'Unknown': '--bs-secondary'
    };
    var FALLBACK_COLOURS = [
        '--bs-primary',
        '--bs-success',
        '--bs-warning',
        '--bs-danger',
        '--bs-info',
        '--bs-secondary'
    ];

    function readData() {
        var element = document.getElementById(DATA_ID);
        if (!element) {
            return null;
        }

        try {
            return JSON.parse(element.textContent);
        } catch (e) {
            return null;
        }
    }

    function getStyle(element) {
        if (typeof getComputedStyle === 'function') {
            return getComputedStyle(element);
        }

        return window.getComputedStyle ? window.getComputedStyle(element) : null;
    }

    function cssVar(name) {
        if (!document.documentElement) {
            return '';
        }

        var styles = getStyle(document.documentElement);
        var value = styles && styles.getPropertyValue ? styles.getPropertyValue(name) : '';

        return value ? String(value).trim() : '';
    }

    function prefersReducedMotion() {
        var query = typeof matchMedia === 'function' ? matchMedia : window.matchMedia;

        return !!(query && query('(prefers-reduced-motion: reduce)').matches);
    }

    function allZero(values) {
        var i;

        for (i = 0; i < values.length; i++) {
            if (Number(values[i])) {
                return false;
            }
        }

        return true;
    }

    function shortDate(label) {
        var value = String(label);
        var day = value.split('T')[0];

        return /^\d{4}-\d{2}-\d{2}$/.test(day) ? day.slice(5) : value;
    }

    function emptyStateFor(canvas) {
        var card = canvas.closest ? canvas.closest('.card') : null;

        return card && card.querySelector ? card.querySelector('[data-empty-state]') : null;
    }

    function toggleEmptyState(canvas, empty) {
        if (!canvas) {
            return;
        }

        var emptyState = emptyStateFor(canvas);
        var wrapper = canvas.parentNode;

        if (empty) {
            if (emptyState && emptyState.classList) {
                emptyState.classList.remove('d-none');
            }
            if (wrapper && wrapper.classList) {
                wrapper.classList.add('d-none');
            }
        } else {
            if (emptyState && emptyState.classList) {
                emptyState.classList.add('d-none');
            }
            if (wrapper && wrapper.classList) {
                wrapper.classList.remove('d-none');
            }
        }
    }

    function destroyChart(id) {
        if (!window.Chart || typeof window.Chart.getChart !== 'function') {
            return;
        }

        var chart = window.Chart.getChart(id);

        if (chart && typeof chart.destroy === 'function') {
            chart.destroy();
        }
    }

    function draw(canvas, config) {
        destroyChart(canvas.id);

        return new window.Chart(canvas, config);
    }

    function dailyConfig(daily, names, colours, animated) {
        var labels = daily.labels || [];
        var orders = daily.orders || [];
        var revenue = daily.revenue || [];

        return {
            type: 'bar',
            data: {
                labels: labels.map(shortDate),
                datasets: [
                    {
                        type: 'bar',
                        label: names.orders,
                        data: orders,
                        backgroundColor: colours.primary,
                        borderColor: colours.primary,
                        yAxisID: 'y'
                    },
                    {
                        type: 'line',
                        label: names.revenue,
                        data: revenue,
                        backgroundColor: colours.success,
                        borderColor: colours.success,
                        fill: false,
                        tension: 0.3,
                        yAxisID: 'y1'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: animated,
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                plugins: {
                    legend: {
                        labels: {
                            color: colours.text
                        }
                    }
                },
                scales: {
                    x: {
                        ticks: {
                            color: colours.text
                        },
                        grid: {
                            color: colours.border
                        }
                    },
                    y: {
                        beginAtZero: true,
                        position: 'left',
                        ticks: {
                            precision: 0,
                            color: colours.text
                        },
                        grid: {
                            color: colours.border
                        }
                    },
                    y1: {
                        beginAtZero: true,
                        position: 'right',
                        ticks: {
                            color: colours.text
                        },
                        grid: {
                            drawOnChartArea: false
                        }
                    }
                }
            }
        };
    }

    function statusConfig(status, colours, animated) {
        var labels = Object.keys(status);
        var values = labels.map(function (label) {
            return status[label];
        });
        var fallback = 0;
        var background = labels.map(function (label) {
            if (Object.prototype.hasOwnProperty.call(STATUS_COLOURS, label)) {
                return cssVar(STATUS_COLOURS[label]);
            }

            return cssVar(FALLBACK_COLOURS[fallback++ % FALLBACK_COLOURS.length]);
        });

        return {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [
                    {
                        data: values,
                        backgroundColor: background
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: animated,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: colours.text
                        }
                    }
                }
            }
        };
    }

    // Dataset names come from the page (translated); fall back to English.
    function datasetNames(data) {
        var names = data && data.names ? data.names : {};

        return {
            orders: names.orders || 'Orders',
            revenue: names.revenue || 'Revenue'
        };
    }

    function drawDaily(canvas, data, colours, animated) {
        var daily = data && data.daily ? data.daily : {};
        var orders = daily.orders || [];
        var revenue = daily.revenue || [];

        if (!canvas || allZero(orders) && allZero(revenue)) {
            toggleEmptyState(canvas, true);
            return;
        }

        toggleEmptyState(canvas, false);
        draw(canvas, dailyConfig(daily, datasetNames(data), colours, animated));
    }

    function drawStatus(canvas, data, colours, animated) {
        var status = data && data.status && typeof data.status === 'object' ? data.status : {};
        var labels = Object.keys(status);
        var values = labels.map(function (label) {
            return status[label];
        });

        if (!canvas || allZero(values)) {
            toggleEmptyState(canvas, true);
            return;
        }

        toggleEmptyState(canvas, false);
        draw(canvas, statusConfig(status, colours, animated));
    }

    function render() {
        var dailyCanvas = document.getElementById(DAILY_ID);
        var statusCanvas = document.getElementById(STATUS_ID);
        var data = readData();

        if (!data || !window.Chart) {
            toggleEmptyState(dailyCanvas, true);
            toggleEmptyState(statusCanvas, true);
            return;
        }

        var colours = {
            primary: cssVar('--bs-primary'),
            success: cssVar('--bs-success'),
            text: cssVar('--bs-body-color'),
            border: cssVar('--bs-border-color')
        };
        var animated = !prefersReducedMotion();

        drawDaily(dailyCanvas, data, colours, animated);
        drawStatus(statusCanvas, data, colours, animated);
    }

    function redraw() {
        destroyChart(DAILY_ID);
        destroyChart(STATUS_ID);
        render();
    }

    function watchTheme() {
        var Observer = typeof MutationObserver === 'function' ? MutationObserver : window.MutationObserver;

        if (!document.documentElement || !Observer) {
            return;
        }

        var observer = new Observer(function (mutations) {
            var i;

            for (i = 0; i < mutations.length; i++) {
                if (mutations[i].attributeName === THEME_ATTRIBUTE) {
                    redraw();
                    return;
                }
            }
        });

        observer.observe(document.documentElement, {
            attributes: true,
            attributeFilter: [THEME_ATTRIBUTE]
        });
    }

    function start() {
        render();
        watchTheme();
    }

    if (document.readyState === 'loading' && document.addEventListener) {
        document.addEventListener('DOMContentLoaded', start);
    } else {
        start();
    }
})();
