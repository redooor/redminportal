/*
 * Image lightbox.
 *
 * Any link with a data-lightbox="group" attribute opens its href in the shared
 * #lightbox-modal (see the layout) instead of a new tab. Links that share a group
 * can be stepped through with the previous and next buttons or the arrow keys.
 * Ctrl, Cmd, Shift and middle clicks, and pages where the script does not run,
 * keep the plain link behaviour.
 */
(function () {
    'use strict';

    var modalElement = document.getElementById('lightbox-modal');

    if (!modalElement || !window.bootstrap) {
        return;
    }

    var modal = window.bootstrap.Modal.getOrCreateInstance(modalElement);
    var image = modalElement.querySelector('#lightbox-image');
    var title = modalElement.querySelector('#lightbox-title');
    var counter = modalElement.querySelector('#lightbox-counter');
    var error = modalElement.querySelector('#lightbox-error');
    var previous = modalElement.querySelector('#lightbox-previous');
    var next = modalElement.querySelector('#lightbox-next');
    var links = [];
    var index = 0;
    var opener = null;

    function show(position) {
        var link = links[position];

        index = position;
        error.classList.add('d-none');
        image.classList.remove('d-none');
        image.src = link.href;
        image.alt = link.getAttribute('data-lightbox-title') || '';
        title.textContent = image.alt;
        counter.textContent = links.length > 1 ? (position + 1) + ' / ' + links.length : '';
        previous.classList.toggle('d-none', links.length < 2);
        next.classList.toggle('d-none', links.length < 2);
    }

    function step(direction) {
        if (links.length > 1) {
            show((index + direction + links.length) % links.length);
        }
    }

    document.addEventListener('click', function (event) {
        var link = event.target.closest('a[data-lightbox]');

        if (!link || event.button !== 0 || event.ctrlKey || event.metaKey || event.shiftKey) {
            return;
        }

        event.preventDefault();

        var group = link.getAttribute('data-lightbox');

        links = Array.prototype.filter.call(document.querySelectorAll('a[data-lightbox]'), function (candidate) {
            return candidate.getAttribute('data-lightbox') === group;
        });
        opener = link;
        show(links.indexOf(link));
        modal.show();
    });

    previous.addEventListener('click', function () { step(-1); });
    next.addEventListener('click', function () { step(1); });

    modalElement.addEventListener('keydown', function (event) {
        if (event.key === 'ArrowLeft') {
            step(-1);
        } else if (event.key === 'ArrowRight') {
            step(1);
        }
    });

    image.addEventListener('error', function () {
        image.classList.add('d-none');
        error.classList.remove('d-none');
    });

    modalElement.addEventListener('hidden.bs.modal', function () {
        image.removeAttribute('src');

        if (opener) {
            opener.focus();
        }
    });
}());
