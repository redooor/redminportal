/*
 * POST links for the admin pages.
 *
 * Links that change data carry data-method="post". A plain click submits a
 * hidden form with the CSRF token from the page's meta tag so the matching
 * route receives a POST. window.redminCsrfToken() exposes the same token to
 * the Ajax callers.
 *
 * Links with the btn-copy class copy their href as an absolute URL instead of
 * opening it, and announce data-copied (or data-copy-failed) politely.
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

    // The container is the open modal when there is one: an aria-modal dialog hides everything outside it.
    function announce(container, message) {
        var region = container.querySelector('.redmin-copy-status');

        if (!region) {
            region = document.createElement('div');
            region.className = 'redmin-copy-status visually-hidden';
            region.setAttribute('role', 'status');
            region.setAttribute('aria-live', 'polite');
            container.appendChild(region);
        }

        // Clear first so repeating the same message is announced again.
        region.textContent = '';
        window.setTimeout(function () {
            region.textContent = message;
        }, 50);
    }

    function copyText(text, container) {
        if (navigator.clipboard && window.isSecureContext) {
            return navigator.clipboard.writeText(text);
        }

        return new Promise(function (resolve, reject) {
            var area = document.createElement('textarea');

            area.value = text;
            area.setAttribute('readonly', '');
            area.style.position = 'fixed';
            area.style.opacity = '0';
            var focused = document.activeElement;

            container.appendChild(area);
            area.select();

            try {
                document.execCommand('copy') ? resolve() : reject(new Error('copy refused'));
            } catch (error) {
                reject(error);
            } finally {
                container.removeChild(area);

                if (focused && typeof focused.focus === 'function') {
                    focused.focus();
                }
            }
        });
    }

    function copyLink(link) {
        var icon = link.querySelector('i');
        var container = link.closest('.modal') || document.body;

        copyText(link.href, container).then(function () {
            announce(container, link.getAttribute('data-copied') || '');

            if (icon) {
                icon.classList.replace('fa-link', 'fa-check');
                window.setTimeout(function () {
                    icon.classList.replace('fa-check', 'fa-link');
                }, 1500);
            }
        }, function () {
            announce(container, link.getAttribute('data-copy-failed') || '');
        });
    }

    function start() {
        document.addEventListener('click', function (event) {
            var target = event.target;
            var copy = target && target.closest ? target.closest('a.btn-copy') : null;

            if (copy && !event.defaultPrevented && isPlainLeftClick(event)) {
                event.preventDefault();
                copyLink(copy);

                return;
            }

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
