/*
 * Modal fragments for the admin pages.
 *
 * A link with data-fragment-modal="#modal-id" opens the modal, fetches the
 * link's href and puts the returned HTML into the modal's [data-fragment-body].
 * Only same-origin links are handled, and only 2xx HTML responses carrying the
 * X-Redmin-Fragment header are trusted, so redirects and login/error pages
 * become the error message, not content. Each modal keeps its own request state.
 */
(function () {
    'use strict';
    if (window.redminFragmentLoaded) return;
    window.redminFragmentLoaded = true;

    var states = new WeakMap();

    function stateOf(modal) {
        var state = states.get(modal);
        if (!state) {
            state = { generation: 0, controller: null, frame: 0, opener: null, toggle: null, hiding: false, pending: null };
            states.set(modal, state);
            modal.addEventListener('hide.bs.modal', function () { state.hiding = true; cancel(modal, state); });
            modal.addEventListener('hidden.bs.modal', function () { hidden(modal, state); });
        }
        return state;
    }
    function plainClick(e) { return e.button === 0 && !e.metaKey && !e.ctrlKey && !e.shiftKey && !e.altKey; }
    function sameOrigin(href) {
        try { return new URL(href, window.location.href).origin === window.location.origin; } catch (e) { return false; }
    }
    // Invalidate the modal's current request: late results of it are ignored.
    function cancel(modal, state) {
        state.generation += 1;
        if (state.controller) state.controller.abort();
        state.controller = null;
        if (state.frame) window.cancelAnimationFrame(state.frame);
        state.frame = 0;
    }
    function setStatus(modal, text) {
        var region = modal.querySelector('[data-fragment-status]');
        if (region) region.textContent = text || '';
    }
    // width null hides the progress bar; any other width shows it.
    function setProgress(modal, width) {
        var wrapper = modal.querySelector('.modal-progress');
        var bar = wrapper && wrapper.querySelector('.progress-bar');
        if (wrapper) wrapper.style.display = width === null ? 'none' : '';
        if (bar) bar.style.width = width === null ? '0%' : width;
    }
    function showProgress(modal, state, gen) {
        setProgress(modal, '0%');
        state.frame = window.requestAnimationFrame(function () {
            state.frame = 0;
            if (gen === state.generation && state.controller) setProgress(modal, '85%');
        });
    }
    function showError(modal, body, message) {
        body.innerHTML = '';
        var alert = document.createElement('div');
        alert.className = 'alert alert-danger';
        alert.setAttribute('role', 'alert');
        alert.appendChild(document.createTextNode(message));
        body.appendChild(alert);
        body.setAttribute('aria-busy', 'false');
        setStatus(modal, message);
    }
    function isFocusable(el) {
        return !!(el && el.isConnected && typeof el.focus === 'function'
            && (el.offsetWidth || el.offsetHeight || el.getClientRects().length));
    }
    function clearModal(modal) {
        var body = modal.querySelector('[data-fragment-body]');
        if (body) { body.innerHTML = ''; body.removeAttribute('aria-busy'); }
        setStatus(modal, '');
        setProgress(modal, null);
    }
    function hidden(modal, state) {
        state.hiding = false;
        clearModal(modal);
        var target = isFocusable(state.toggle) ? state.toggle : (isFocusable(state.opener) ? state.opener : document.body);
        var pending = state.pending;
        state.opener = null;
        state.toggle = null;
        state.pending = null;
        // Bootstrap ignores show() while a modal is hiding, so a reopen that came in then starts now.
        if (pending) { load(pending, modal); return; }
        target.focus();
    }
    function load(link, modal) {
        var body = modal.querySelector('[data-fragment-body]');
        if (!body) return;
        var state = stateOf(modal);
        if (state.hiding) { state.pending = link; return; }
        cancel(modal, state);
        var gen = state.generation;
        var group = link.closest('.dropdown, .dropup, .dropstart, .dropend, .btn-group');
        state.opener = link;
        state.toggle = group ? group.querySelector('[data-bs-toggle="dropdown"]') : null;
        body.setAttribute('aria-busy', 'true');
        body.innerHTML = '';
        setStatus(modal, body.getAttribute('data-fragment-loading'));
        var controller = new AbortController();
        state.controller = controller;
        showProgress(modal, state, gen);
        bootstrap.Modal.getOrCreateInstance(modal).show();
        fetch(link.href, {
            credentials: 'same-origin',
            headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'text/html' },
            signal: controller.signal
        }).then(function (response) {
            var type = (response.headers.get('Content-Type') || '').toLowerCase();
            var trusted = response.ok && !response.redirected && type.indexOf('text/html') !== -1
                && response.headers.get('X-Redmin-Fragment') === '1';
            if (!trusted) throw new Error('Not a fragment response');
            return response.text();
        }).then(function (text) {
            // Hiding or a newer open of this modal bumps the generation, so a stale result stops here.
            if (gen !== state.generation) return;
            body.innerHTML = text;
            body.setAttribute('aria-busy', 'false');
            setStatus(modal, body.getAttribute('data-fragment-loaded'));
        }).catch(function (error) {
            if ((error && error.name === 'AbortError') || gen !== state.generation) return;
            showError(modal, body, body.getAttribute('data-fragment-error') || '');
        }).finally(function () {
            if (gen !== state.generation) return;
            if (state.controller === controller) state.controller = null;
            if (state.frame) { window.cancelAnimationFrame(state.frame); state.frame = 0; }
            setProgress(modal, null);
        });
    }

    document.addEventListener('click', function (event) {
        var target = event.target;
        var link = target && target.closest ? target.closest('a[data-fragment-modal]') : null;
        if (!link || event.defaultPrevented || !plainClick(event)) return;
        var modal = document.querySelector(link.getAttribute('data-fragment-modal'));
        if (!modal || !sameOrigin(link.href)) return;
        event.preventDefault();
        load(link, modal);
    });

    // A page restored from the back/forward cache must not show old data or keep Bootstrap's scroll lock:
    // clear the fragment modals and reload if one was open.
    window.addEventListener('pageshow', function (event) {
        if (!event.persisted) return;
        var modals = document.querySelectorAll('.modal');
        var wasOpen = false;
        for (var i = 0; i < modals.length; i++) {
            if (!modals[i].querySelector('[data-fragment-body]')) continue;
            if (modals[i].classList.contains('show')) wasOpen = true;
            cancel(modals[i], stateOf(modals[i]));
            clearModal(modals[i]);
        }
        if (wasOpen) window.location.reload();
    });
})();
