/*
 * Redmin file input: image/file chooser with a preview and a remove button.
 * Replaces Jasny's fileupload plugin. The submitted payload is the plain file input (name="image"), nothing else.
 *
 * Markup:
 *   <div class="redmin-fileinput" data-redmin-fileinput>
 *     <div class="redmin-fileinput-preview img-thumbnail"></div>
 *     <label class="btn btn-secondary">
 *       <span class="redmin-fileinput-new">Select</span><span class="redmin-fileinput-exists">Change</span>
 *       <input name="image" type="file" class="visually-hidden">
 *     </label>
 *     <button type="button" class="btn btn-danger redmin-fileinput-exists" data-redmin-fileinput-clear>Remove</button>
 *   </div>
 */
(function () {
    'use strict';

    var ROOT = '[data-redmin-fileinput]';

    function releaseUrl(root) {
        if (root._redminUrl) {
            URL.revokeObjectURL(root._redminUrl);
            root._redminUrl = null;
        }
    }

    function reset(root) {
        releaseUrl(root);
        root.querySelector('input[type=file]').value = '';
        root.querySelector('.redmin-fileinput-preview').textContent = '';
        root.classList.remove('is-set');
    }

    function render(root) {
        var input = root.querySelector('input[type=file]');
        var preview = root.querySelector('.redmin-fileinput-preview');
        var file = input.files && input.files[0];

        releaseUrl(root);
        preview.textContent = '';
        if (!file) {
            root.classList.remove('is-set');
            return;
        }
        if (/^image\//.test(file.type)) {
            var img = document.createElement('img');
            root._redminUrl = URL.createObjectURL(file);
            img.src = root._redminUrl;
            img.alt = file.name;
            preview.appendChild(img);
        } else {
            var name = document.createElement('span');
            name.className = 'redmin-fileinput-filename';
            name.textContent = file.name;
            preview.appendChild(name);
        }
        root.classList.add('is-set');
    }

    document.addEventListener('change', function (e) {
        var root = e.target.closest && e.target.closest(ROOT);
        if (root && e.target.type === 'file') {
            render(root);
        }
    });

    document.addEventListener('click', function (e) {
        var clear = e.target.closest && e.target.closest('[data-redmin-fileinput-clear]');
        if (clear) {
            e.preventDefault();
            reset(clear.closest(ROOT));
        }
    });

    // A form reset clears the chosen file, so drop the preview with it.
    document.addEventListener('reset', function (e) {
        Array.prototype.forEach.call(e.target.querySelectorAll(ROOT), reset);
    }, true);

    // Back/forward navigation can restore a chosen file without its preview.
    window.addEventListener('pageshow', function () {
        Array.prototype.forEach.call(document.querySelectorAll(ROOT), render);
    });
})();
