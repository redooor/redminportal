/*
 * Redmin file input: image/file chooser with a preview and a remove button.
 * Replaces Jasny's fileupload plugin. The submitted payload is the plain file input (name="images[]"), nothing else.
 *
 * Markup:
 *   <div class="redmin-fileinput" data-redmin-fileinput>
 *     <div class="redmin-fileinput-preview img-thumbnail"></div>
 *     <label class="btn btn-secondary">
 *       <span class="redmin-fileinput-new">Select Images</span><span class="redmin-fileinput-exists">Change</span>
 *       <input name="images[]" type="file" multiple accept="image/*" class="visually-hidden">
 *     </label>
 *     <button type="button" class="btn btn-danger redmin-fileinput-exists" data-redmin-fileinput-clear>Remove</button>
 *   </div>
 */
(function () {
    'use strict';

    var ROOT = '[data-redmin-fileinput]';

    function releaseUrls(root) {
        var urls = root._redminUrls || [];

        urls.forEach(function (url) {
            URL.revokeObjectURL(url);
        });
        root._redminUrls = [];
    }

    function reset(root) {
        releaseUrls(root);
        root.querySelector('input[type=file]').value = '';
        root.querySelector('.redmin-fileinput-preview').textContent = '';
        root.classList.remove('is-set');
    }

    function render(root) {
        var input = root.querySelector('input[type=file]');
        var preview = root.querySelector('.redmin-fileinput-preview');
        var files = input && input.files ? Array.prototype.slice.call(input.files) : [];

        releaseUrls(root);
        preview.textContent = '';
        if (!files.length) {
            root.classList.remove('is-set');
            return;
        }
        files.forEach(function (file) {
            if (/^image\//.test(file.type)) {
                var img = document.createElement('img');
                var url = URL.createObjectURL(file);
                root._redminUrls.push(url);
                img.src = url;
                img.alt = file.name;
                preview.appendChild(img);
            } else {
                var name = document.createElement('span');
                name.className = 'redmin-fileinput-filename';
                name.textContent = file.name;
                preview.appendChild(name);
            }
        });
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

    // A form reset clears the chosen files, so drop the preview with them.
    document.addEventListener('reset', function (e) {
        Array.prototype.forEach.call(e.target.querySelectorAll(ROOT), reset);
    }, true);

    // Back/forward navigation can restore chosen files without their preview.
    window.addEventListener('pageshow', function () {
        Array.prototype.forEach.call(document.querySelectorAll(ROOT), render);
    });
})();
