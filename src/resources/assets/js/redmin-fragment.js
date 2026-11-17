/*
 * Modal fragments for the admin pages.
 *
 * A link with data-fragment-modal="#modal-id" opens the modal, fetches the
 * link's href and puts the returned HTML into the modal's [data-fragment-body].
 * A [data-fragment-modal] link inside a fragment body navigates that same modal
 * without replacing the remembered external opener. A form with
 * data-fragment-form inside a body is posted with fetch and FormData, following
 * redirects; a[data-fragment-post] posts directly, and data-fragment-confirm
 * (with data-fragment-confirm-yes / data-fragment-confirm-no) asks in place
 * first. Only same-origin requests are handled. Responses replacing the body
 * are trusted only when they are 2xx HTML with X-Redmin-Fragment: 1 and a
 * same-origin final URL, so redirects and login/error pages become the error
 * message, not content. GET fragment links still reject redirects. A POST whose
 * network outcome is unknown shows data-fragment-unknown (falling back to
 * data-fragment-error) and is never retried. Before a body is cleared or
 * replaced it dispatches redmin:fragment-clear and after new content
 * redmin:fragment-loaded (both bubbling, detail: {modal, body, url, method});
 * an unknown POST outcome also dispatches redmin:fragment-unknown on the body.
 * Each modal keeps its own request and mutation state.
 */
(function () {
    'use strict';
    if (window.redminFragmentLoaded) return;
    window.redminFragmentLoaded = true;

    var states = new WeakMap();

    function stateOf(modal) {
        var state = states.get(modal);
        if (!state) {
            state = {
                generation: 0,
                controller: null,
                frame: 0,
                opener: null,
                toggle: null,
                hiding: false,
                pending: null,
                pendingInternal: false,
                mutating: false,
                unknown: false,
                contentUrl: null,
                contentMethod: null
            };
            states.set(modal, state);
            modal.addEventListener('hide.bs.modal', function (event) {
                // A POST cannot be cancelled once it was sent: keep the modal open until its outcome is known.
                if (state.mutating) { event.preventDefault(); return; }
                state.hiding = true;
                cancel(modal, state);
            });
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
    function detail(modal, body, url, method) {
        return { modal: modal, body: body, url: url || null, method: method || null };
    }
    function dispatch(body, name, data) {
        if (body) body.dispatchEvent(new CustomEvent(name, { bubbles: true, detail: data }));
    }
    function markLoaded(modal, body, state, url, method) {
        state.contentUrl = url;
        state.contentMethod = method;
        dispatch(body, 'redmin:fragment-loaded', detail(modal, body, url, method));
    }
    // The body element is cleared, not replaced, so let pages tear their widgets down first.
    function clearBody(modal, body, state, url, method) {
        if (!body) return;
        var clearUrl = url === undefined ? (state ? state.contentUrl : null) : url;
        var clearMethod = method === undefined ? (state ? state.contentMethod : null) : method;
        dispatch(body, 'redmin:fragment-clear', detail(modal, body, clearUrl, clearMethod));
        if (typeof window.redminFileinputRelease === 'function') window.redminFileinputRelease(body);
        body.innerHTML = '';
        if (state) {
            state.contentUrl = null;
            state.contentMethod = null;
        }
    }
    function focusFirst(modal) {
        var body = modal.querySelector('[data-fragment-body]');
        var target = body ? body.querySelector('[data-fragment-title], h1, h2, h3') : null;
        if (!target) target = modal.querySelector('.modal-title');
        if (!target || typeof target.focus !== 'function') return;
        if (!target.hasAttribute('tabindex')) target.setAttribute('tabindex', '-1');
        target.focus();
    }
    function showError(modal, body, message, url, method) {
        clearBody(modal, body, states.get(modal), url, method);
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
        if (body) {
            clearBody(modal, body, states.get(modal));
            body.removeAttribute('aria-busy');
        }
        setStatus(modal, '');
        setProgress(modal, null);
    }
    function hidden(modal, state) {
        state.hiding = false;
        clearModal(modal);
        var target = isFocusable(state.toggle) ? state.toggle : (isFocusable(state.opener) ? state.opener : document.body);
        var pending = state.pending;
        var pendingInternal = state.pendingInternal;
        state.opener = null;
        state.toggle = null;
        state.pending = null;
        state.pendingInternal = false;
        // Bootstrap ignores show() while a modal is hiding, so a reopen that came in then starts now.
        if (pending) { load(pending, modal, pendingInternal); return; }
        target.focus();
    }
    function load(link, modal, internal) {
        var body = modal.querySelector('[data-fragment-body]');
        if (!body) return;
        var state = stateOf(modal);
        if (state.hiding) { state.pending = link; state.pendingInternal = !!internal; return; }
        if (state.mutating) return;
        cancel(modal, state);
        var gen = state.generation;
        // An internal link keeps the external opener and toggle remembered for focus return.
        if (!internal) {
            var group = link.closest('.dropdown, .dropup, .dropstart, .dropend, .btn-group');
            state.opener = link;
            state.toggle = group ? group.querySelector('[data-bs-toggle="dropdown"]') : null;
        }
        body.setAttribute('aria-busy', 'true');
        clearBody(modal, body, state);
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
            state.unknown = false;
            markLoaded(modal, body, state, link.href, 'GET');
            if (internal) focusFirst(modal);
        }).catch(function (error) {
            if ((error && error.name === 'AbortError') || gen !== state.generation) return;
            state.unknown = false;
            showError(modal, body, body.getAttribute('data-fragment-error') || '', link.href, 'GET');
        }).finally(function () {
            if (gen !== state.generation) return;
            if (state.controller === controller) state.controller = null;
            if (state.frame) { window.cancelAnimationFrame(state.frame); state.frame = 0; }
            setProgress(modal, null);
        });
    }

    function csrfToken() {
        if (typeof window.redminCsrfToken === 'function') {
            try { return window.redminCsrfToken() || ''; } catch (e) { /* fall through to the meta tag */ }
        }
        var meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? (meta.getAttribute('content') || '') : '';
    }
    function disableControls(form, extra) {
        var saved = [];
        var controls = form ? form.querySelectorAll('button, input[type=submit]') : [];
        var i;
        for (i = 0; i < controls.length; i++) {
            if (!controls[i].disabled) { controls[i].disabled = true; saved.push(controls[i]); }
        }
        if (extra) {
            for (i = 0; i < extra.length; i++) {
                if (extra[i] && !extra[i].disabled) { extra[i].disabled = true; saved.push(extra[i]); }
            }
        }
        return saved;
    }
    function restoreControls(saved) {
        if (!saved) return;
        for (var i = 0; i < saved.length; i++) saved[i].disabled = false;
    }
    function formDataFor(form, submitter) {
        if (submitter) {
            try { return new FormData(form, submitter); } catch (e) { /* older browser */ }
        }
        return new FormData(form);
    }
    // POSTs for forms and inline-confirm links. It deliberately does not use the content AbortController:
    // aborting cannot cancel the server, so a sent mutation stays tracked until its outcome is known.
    function postTo(modal, body, state, url, method, data, form, extraControls) {
        if (!body || state.mutating) return;
        var gen = state.generation;
        var controls = disableControls(form, extraControls);
        state.mutating = true;
        state.unknown = false;
        body.setAttribute('aria-busy', 'true');
        fetch(url, {
            method: method,
            body: data,
            credentials: 'same-origin',
            headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'text/html' }
        }).then(function (response) {
            var type = (response.headers.get('Content-Type') || '').toLowerCase();
            var trusted = response.ok && type.indexOf('text/html') !== -1
                && response.headers.get('X-Redmin-Fragment') === '1'
                && sameOrigin(response.url);
            if (!trusted) return { failed: true };
            return response.text().then(function (text) {
                return { text: text, url: response.url || url };
            });
        }).then(function (result) {
            if (gen !== state.generation) return;
            if (result.failed) {
                state.unknown = false;
                showError(modal, body, body.getAttribute('data-fragment-error') || '', url, method);
                return;
            }
            clearBody(modal, body, state, result.url, method);
            body.innerHTML = result.text;
            body.setAttribute('aria-busy', 'false');
            setStatus(modal, body.getAttribute('data-fragment-loaded'));
            state.unknown = false;
            markLoaded(modal, body, state, result.url, method);
            focusFirst(modal);
        }).catch(function () {
            if (gen !== state.generation) return;
            // The request was sent but no answer was read: it may or may not have saved.
            var message = body.getAttribute('data-fragment-unknown') || body.getAttribute('data-fragment-error') || '';
            state.unknown = true;
            showError(modal, body, message, url, method);
            restoreControls(controls);
            dispatch(body, 'redmin:fragment-unknown', detail(modal, body, url, method));
        }).finally(function () {
            state.mutating = false;
            restoreControls(controls);
            if (gen === state.generation) body.setAttribute('aria-busy', 'false');
        });
    }
    function inlineConfirm(link, modal, body, state) {
        link.style.display = 'none';
        var group = document.createElement('span');
        group.className = 'fragment-confirm';
        group.setAttribute('role', 'group');
        group.appendChild(document.createTextNode(link.getAttribute('data-fragment-confirm') || ''));
        var yes = document.createElement('button');
        yes.type = 'button';
        yes.className = 'btn btn-danger btn-sm ms-2';
        yes.textContent = link.getAttribute('data-fragment-confirm-yes') || 'OK';
        var no = document.createElement('button');
        no.type = 'button';
        no.className = 'btn btn-outline-secondary btn-sm ms-1';
        no.textContent = link.getAttribute('data-fragment-confirm-no') || 'Cancel';
        group.appendChild(yes);
        group.appendChild(no);
        link.parentNode.insertBefore(group, link.nextSibling);
        no.focus();
        no.addEventListener('click', function () {
            group.remove();
            link.style.display = '';
            link.focus();
        });
        yes.addEventListener('click', function () {
            if (state.mutating) return;
            var data = new FormData();
            var token = csrfToken();
            if (token) data.append('_token', token);
            postTo(modal, body, state, link.href, 'POST', data, null, [yes, no]);
        });
    }

    document.addEventListener('click', function (event) {
        var target = event.target;
        var link = target && target.closest ? target.closest('a[data-fragment-modal]') : null;
        if (!link || event.defaultPrevented || !plainClick(event)) return;
        var modal = document.querySelector(link.getAttribute('data-fragment-modal'));
        if (!modal || !sameOrigin(link.href)) return;
        var body = link.closest('[data-fragment-body]');
        var internal = !!(body && modal.contains(body));
        var state = stateOf(modal);
        if (state.mutating) { event.preventDefault(); return; }
        event.preventDefault();
        load(link, modal, internal);
    });

    document.addEventListener('submit', function (event) {
        var form = event.target;
        if (!form || !form.matches || !form.matches('form[data-fragment-form]')) return;
        var body = form.closest('[data-fragment-body]');
        if (!body) return;
        var modal = body.closest('.modal');
        if (!modal) return;
        var state = stateOf(modal);
        if (event.defaultPrevented) return;
        if (state.mutating) { event.preventDefault(); return; }
        // A cross-origin action is left to the browser.
        if (!sameOrigin(form.action)) return;
        event.preventDefault();
        var method = (form.method || 'POST').toUpperCase();
        postTo(modal, body, state, form.action, method, formDataFor(form, event.submitter), form);
    });

    document.addEventListener('click', function (event) {
        var target = event.target;
        var link = target && target.closest ? target.closest('a[data-fragment-post]') : null;
        if (!link || event.defaultPrevented || !plainClick(event)) return;
        var body = link.closest('[data-fragment-body]');
        if (!body) return;
        var modal = body.closest('.modal');
        if (!modal || !sameOrigin(link.href)) return;
        event.preventDefault();
        var state = stateOf(modal);
        if (state.mutating) return;
        if (link.getAttribute('data-fragment-confirm')) {
            inlineConfirm(link, modal, body, state);
            return;
        }
        var data = new FormData();
        var token = csrfToken();
        if (token) data.append('_token', token);
        postTo(modal, body, state, link.href, 'POST', data, null);
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
