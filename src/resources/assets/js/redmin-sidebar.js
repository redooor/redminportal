/*
 * Collapsible admin sidebar.
 *
 * The inline script in the layout <head> adds html.sidebar-open before the
 * page paints when the sidebar should start open. This script keeps the
 * toggle's ARIA state, the sidebar's inert state and the remembered desktop
 * choice in sync, handles focus, Escape and the desktop breakpoint, and sends
 * a resize event once the layout transition finishes so Chart.js and Jodit
 * re-measure themselves.
 */
(function () {
    'use strict';

    var DESKTOP_QUERY = '(min-width: 992px)';
    var STORAGE_KEY = 'redmin.sidebar';

    function desktopQuery() {
        return typeof window.matchMedia === 'function' ? window.matchMedia(DESKTOP_QUERY) : null;
    }

    function isDesktop() {
        var query = desktopQuery();

        return query ? query.matches : window.innerWidth >= 992;
    }

    function isOpen() {
        return document.documentElement.classList.contains('sidebar-open');
    }

    function getSidebar() {
        return document.getElementById('sidebar');
    }

    function getToggle() {
        return document.getElementById('sidebar-toggle');
    }

    function getMainContent() {
        return document.querySelector('.main-content');
    }

    function getHeader() {
        return document.getElementById('header');
    }

    // Links and buttons that can take focus inside the open drawer, in tab order.
    function drawerFocusables() {
        var sidebar = getSidebar();

        return sidebar ? Array.prototype.slice.call(sidebar.querySelectorAll('a[href], button:not([disabled])')).filter(function (el) {
            return el.getClientRects().length > 0;
        }) : [];
    }

    // Never let a disabled storage (private mode, blocked cookies) break the page.
    function readStored() {
        try {
            return window.localStorage.getItem(STORAGE_KEY);
        } catch (e) {
            return null;
        }
    }

    function store(open) {
        try {
            window.localStorage.setItem(STORAGE_KEY, open ? 'open' : 'closed');
        } catch (e) {
            // The toggle still works for this page view.
        }
    }

    function initialOpen() {
        return isDesktop() && readStored() !== 'closed';
    }

    function firstLink() {
        var sidebar = getSidebar();

        return sidebar && sidebar.querySelector ? sidebar.querySelector('a[href]') : null;
    }

    function setInert(el, on) {
        if (!el) {
            return;
        }
        if (on) {
            el.setAttribute('inert', '');
        } else {
            el.removeAttribute('inert');
        }
    }

    function applyState(open, returnFocus, forceFocus) {
        var sidebar = getSidebar();
        var toggle = getToggle();
        var main = getMainContent();
        var header = getHeader();
        var mobile = !isDesktop();
        var trap = open && mobile;

        // The page behind the open drawer must not take focus; lift that first so the toggle can receive it on close.
        if (!trap) {
            setInert(main, false);
            setInert(header, false);
        }

        // Move focus out of the drawer before it turns inert, otherwise it is lost to the document.
        if (!open && returnFocus && sidebar && toggle && (forceFocus || sidebar.contains(document.activeElement))) {
            toggle.focus();
        }

        document.documentElement.classList.toggle('sidebar-open', open);

        if (toggle) {
            var label = open ? toggle.getAttribute('data-label-open') : toggle.getAttribute('data-label-closed');

            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
            if (label) {
                toggle.setAttribute('aria-label', label);
            }
        }

        if (sidebar) {
            setInert(sidebar, !open);
            if (open) {
                sidebar.removeAttribute('aria-hidden');
            } else {
                sidebar.setAttribute('aria-hidden', 'true');
            }
        }

        if (trap) {
            // The open drawer covers the page: keep Tab and screen readers inside it (the navbar toggle is under it too).
            setInert(main, true);
            setInert(header, true);

            var link = firstLink();

            if (link && typeof link.focus === 'function') {
                link.focus();
            }
        }
    }

    function announceLayoutChange() {
        var sidebar = getSidebar();
        var done = false;

        function finish() {
            if (done) {
                return;
            }
            done = true;
            if (sidebar && sidebar.removeEventListener) {
                sidebar.removeEventListener('transitionend', onTransitionEnd);
            }
            window.dispatchEvent(new Event('resize'));
        }

        function onTransitionEnd(event) {
            if (event.target === sidebar && event.propertyName === 'transform') {
                finish();
            }
        }

        if (sidebar && sidebar.addEventListener) {
            sidebar.addEventListener('transitionend', onTransitionEnd);
        }

        // Reduced motion (or no transition at all) never fires transitionend.
        window.setTimeout(finish, 400);
    }

    function setOpen(open, persist, forceFocus) {
        var changed = isOpen() !== open;

        applyState(open, true, forceFocus);

        if (persist && isDesktop()) {
            store(open);
        }

        if (changed) {
            announceLayoutChange();
        }
    }

    function start() {
        applyState(isOpen(), false);

        document.addEventListener('click', function (event) {
            var target = event.target;
            var toggle = target && target.closest ? target.closest('.sidebar-toggle') : null;

            if (!toggle) {
                return;
            }

            event.preventDefault();
            event.stopPropagation();
            // A backdrop click can blur the focused sidebar link before the click handler runs, so return focus explicitly.
            setOpen(!isOpen(), true, toggle.id === 'sidebar-overlay');
        });

        document.addEventListener('keydown', function (event) {
            if (!isOpen() || isDesktop()) {
                return;
            }

            if (event.key === 'Escape' || event.keyCode === 27) {
                event.preventDefault();
                setOpen(false, false);
            } else if (event.key === 'Tab' || event.keyCode === 9) {
                // Keep Tab cycling inside the open drawer.
                var items = drawerFocusables();
                var first = items[0];
                var last = items[items.length - 1];

                if (!first) {
                    return;
                }
                if (event.shiftKey && (document.activeElement === first || !getSidebar().contains(document.activeElement))) {
                    event.preventDefault();
                    last.focus();
                } else if (!event.shiftKey && (document.activeElement === last || !getSidebar().contains(document.activeElement))) {
                    event.preventDefault();
                    first.focus();
                }
            }
        });

        var query = desktopQuery();

        if (query) {
            var onBreakpointChange = function () {
                // Crossing the breakpoint re-applies the initial rule for the new side.
                setOpen(initialOpen(), false);
            };

            if (query.addEventListener) {
                query.addEventListener('change', onBreakpointChange);
            } else if (query.addListener) {
                query.addListener(onBreakpointChange);
            }
        }
    }

    if (document.readyState === 'loading' && document.addEventListener) {
        document.addEventListener('DOMContentLoaded', start);
    } else {
        start();
    }
})();
