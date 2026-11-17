/*
 * Modal fragments for the admin pages.
 *
 * A link with data-fragment-modal="#modal-id" opens the modal, fetches the
 * link's href and puts the returned HTML into the modal's [data-fragment-body].
 * Only same-origin HTML carrying the X-Redmin-Fragment header is trusted, so
 * redirects and login/error pages become the error message, not content.
 */
(function () {
    'use strict';
    if (window.redminFragmentLoaded) return;
    window.redminFragmentLoaded = true;

    var generation = 0, controller = null, openers = new WeakMap(), listening = new WeakSet();
    function plainClick(e) { return e.button === 0 && !e.metaKey && !e.ctrlKey && !e.shiftKey && !e.altKey; }
    function abortRequest() { if (controller) controller.abort(); controller = null; }
    // Hiding the modal bumps the generation, so a matching number means the modal is still wanted.
    // The modal's own 'show' class is not checked: it only appears after the backdrop fade, which a fast response can beat.
    function isCurrent(gen) { return gen === generation; }
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
    function showProgress(modal, gen) {
        setProgress(modal, '0%');
        window.requestAnimationFrame(function () { if (gen === generation) setProgress(modal, '85%'); });
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
    function returnFocus(entry) {
        if (!entry) return;
        var target = isFocusable(entry.toggle) ? entry.toggle
            : (isFocusable(entry.opener) ? entry.opener : document.body);
        target.focus();
    }
    function clearModal(modal) {
        var body = modal.querySelector('[data-fragment-body]');
        if (body) { body.innerHTML = ''; body.removeAttribute('aria-busy'); }
        setStatus(modal, '');
        setProgress(modal, null);
    }
    function listen(modal) {
        if (listening.has(modal)) return;
        listening.add(modal);
        modal.addEventListener('hide.bs.modal', function () { generation += 1; abortRequest(); });
        modal.addEventListener('hidden.bs.modal', function () {
            clearModal(modal);
            returnFocus(openers.get(modal));
            openers.delete(modal);
        });
    }
    function closeWithoutAnimation(modal) {
        if (!modal.classList.contains('show')) return;
        modal.classList.remove('show');
        modal.style.display = 'none';
        modal.setAttribute('aria-hidden', 'true');
        modal.removeAttribute('aria-modal');
        document.body.classList.remove('modal-open');
        document.body.style.removeProperty('padding-right');
        var backdrops = document.querySelectorAll('.modal-backdrop');
        for (var i = 0; i < backdrops.length; i++) backdrops[i].parentNode.removeChild(backdrops[i]);
        var instance = window.bootstrap && bootstrap.Modal ? bootstrap.Modal.getInstance(modal) : null;
        if (instance) instance.dispose();
    }
    function load(link, modal) {
        var body = modal.querySelector('[data-fragment-body]');
        if (!body) return;
        var gen = ++generation;
        var dropdown = link.closest('.dropdown');
        var toggle = dropdown ? dropdown.querySelector('.dropdown-toggle') : null;
        abortRequest();
        openers.set(modal, { opener: link, toggle: toggle });
        listen(modal);
        body.setAttribute('aria-busy', 'true');
        body.innerHTML = '';
        setStatus(modal, body.getAttribute('data-fragment-loading'));
        showProgress(modal, gen);
        bootstrap.Modal.getOrCreateInstance(modal).show();
        controller = new AbortController();
        var currentController = controller;
        fetch(link.href, {
            credentials: 'same-origin',
            headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'text/html' },
            signal: currentController.signal
        }).then(function (response) {
            var type = (response.headers.get('Content-Type') || '').toLowerCase();
            var trusted = response.ok && !response.redirected && type.indexOf('text/html') !== -1
                && response.headers.get('X-Redmin-Fragment') === '1';
            if (!trusted) throw new Error('Not a fragment response');
            return response.text();
        }).then(function (text) {
            if (!isCurrent(gen)) return;
            body.innerHTML = text;
            body.setAttribute('aria-busy', 'false');
            setStatus(modal, body.getAttribute('data-fragment-loaded'));
        }).catch(function (error) {
            if (error && error.name === 'AbortError') return;
            if (!isCurrent(gen)) return;
            showError(modal, body, body.getAttribute('data-fragment-error') || '');
        }).finally(function () {
            if (gen === generation) setProgress(modal, null);
            if (controller === currentController) controller = null;
        });
    }

    document.addEventListener('click', function (event) {
        var target = event.target;
        var link = target && target.closest ? target.closest('a[data-fragment-modal]') : null;
        if (!link || event.defaultPrevented || !plainClick(event)) return;
        var modal = document.querySelector(link.getAttribute('data-fragment-modal'));
        if (!modal) return;
        event.preventDefault();
        load(link, modal);
    });

    window.addEventListener('pageshow', function (event) {
        if (!event.persisted) return;
        generation += 1;
        abortRequest();
        var modals = document.querySelectorAll('.modal');
        for (var i = 0; i < modals.length; i++) {
            if (!modals[i].querySelector('[data-fragment-body]')) continue;
            clearModal(modals[i]);
            closeWithoutAnimation(modals[i]);
            openers.delete(modals[i]);
        }
    });
})();