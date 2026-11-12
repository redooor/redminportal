/*
 * POST links for the admin pages.
 *
 * Links that change data carry data-method="post". A plain click submits a
 * hidden form with the CSRF token from the page's meta tag so the matching
 * route receives a POST. window.redminCsrfToken() exposes the same token to
 * the Ajax callers.
 */
(function () {
    'use strict';

    function csrfToken() {
        var meta = document.querySelector('meta[name="csrf-token"]');

        return meta ? meta.getAttribute('content') : '';
    }

    function isPlainLeftClick(event) {
        return event.button === 0 && !event.metaKey && !event.ctrlKey && !event.shiftKey && !event.altKey;
    }

    function post(link) {
        var form = document.createElement('form');
        var input = document.createElement('input');

        form.method = 'post';
        form.action = link.href;
        form.style.display = 'none';

        input.type = 'hidden';
        input.name = '_token';
        input.value = csrfToken();

        form.appendChild(input);
        document.body.appendChild(form);
        form.submit();
    }

    window.redminCsrfToken = csrfToken;

    function start() {
        document.addEventListener('click', function (event) {
            var target = event.target;
            var link = target && target.closest ? target.closest('a[data-method="post"]') : null;

            // Links with their own handler (modal or Ajax) have already claimed the click.
            if (!link || event.defaultPrevented || !isPlainLeftClick(event)) {
                return;
            }

            event.preventDefault();
            post(link);
        });
    }

    if (document.readyState === 'complete') {
        start();
    } else {
        // Wait for DOMContentLoaded so the page's own click handlers are registered first.
        document.addEventListener('DOMContentLoaded', start);
    }
})();
