{{--
    Rich text editor (Jodit) for every textarea on the page
    --------------------------------
    Usage Example:
    --------------------------------
    @include('redminportal::plugins/editor', [
        'editorImages' => 'optional images for insertion'
    ])
    --------------------------------
    Content CSS is read from config('redminportal::tinymce') (the key keeps its name so published configs still work).

    Call window.redminInitEditors(root) after injecting a fragment (an Element or document) and
    window.redminDestroyEditors(root) before removing it. Images for an editor come from the nearest
    [data-editor-images] ancestor (JSON); page-mode editors fall back to the images passed to this include.
--}}
<link rel="stylesheet" href="{{ URL::to('vendor/redooor/redminportal/css/jodit/jodit.min.css') }}">
<script src="{{ URL::to('vendor/redooor/redminportal/js/jodit/jodit.min.js') }}"></script>
<script>
    !function () {
        var pageImages = [
        @foreach (($editorImages ?? $tinyImages ?? []) as $editorImage)
            {
                title: @json(pathinfo($editorImage->path)['filename']),
                value: @json(url(Redminportal::imagine()->getUrl($editorImage->path, 'large')))
            },
        @endforeach
        ];

        // Insert an image uploaded to this record (the "media library" of the record being edited).
        // The list is per editor (editor.o.redminImages), so a fragment never sees the page's images.
        Jodit.defaultOptions.controls.media = {
            icon: 'folder',
            tooltip: 'Insert uploaded image',
            isDisabled: function (editor) {
                return !editor.o.redminImages || editor.o.redminImages.length === 0;
            },
            popup: function (editor, current, close) {
                var images = editor.o.redminImages || [];
                var list = editor.create.div('redmin-editor-media');
                images.forEach(function (image) {
                    var item = editor.create.element('button', { type: 'button', class: 'redmin-editor-media-item', title: image.title });
                    var thumb = editor.create.element('img', { src: image.value, alt: image.title });
                    item.appendChild(thumb);
                    item.addEventListener('click', function () {
                        var img = editor.createInside.element('img');
                        img.setAttribute('src', image.value);
                        img.setAttribute('alt', image.title);
                        editor.s.insertImage(img, null, null);
                        close();
                    });
                    list.appendChild(item);
                });
                return list;
            }
        };

        var cssLinks = [
        @foreach (config('redminportal::tinymce') as $editorCss)
            {{-- published configs from the TinyMCE era still list the old content stylesheet --}}
            @json(url(str_replace('redmin-tinymce.min.css', 'redmin-editor.min.css', $editorCss))),
        @endforeach
        ];

        var editors = [];

        function imagesFor(textarea, root) {
            var holder = textarea.closest ? textarea.closest('[data-editor-images]') : null;

            if (holder) {
                try {
                    var parsed = JSON.parse(holder.getAttribute('data-editor-images') || '[]');

                    return Array.isArray(parsed) ? parsed : [];
                } catch (e) {
                    return [];
                }
            }

            // Page mode only: a fragment with no attribute must not inherit the page's images.
            return root === document ? pageImages : [];
        }

        function optionsFor(textarea, root) {
            var options = {
                height: 360,
                iframe: true,
                iframeCSSLinks: cssLinks,
                buttons: 'undo,redo,|,paragraph,|,bold,italic,|,align,|,ul,ol,|,outdent,indent,|,link,|,media,image,|,source',
                showCharsCounter: false,
                showWordsCounter: false,
                showXPathInStatusbar: false,
                askBeforePasteHTML: false,
                defaultActionOnPaste: 'insert_as_html',
                toolbarAdaptive: true,
                // Plain textarea for source mode: the default 'ace' editor and HTML beautifier are fetched from a public CDN
                sourceEditor: 'area',
                beautifyHTML: false,
                // An emptied editor must submit '' (not '<p><br></p>') so `required` validation still triggers
                cleanHTML: { collapseEmptyValueToEmptyString: true },
                redminImages: imagesFor(textarea, root)
            };

            // Jodit pop-ups and dialogs must stay inside the Bootstrap modal's focus trap.
            var modal = textarea.closest ? textarea.closest('.modal') : null;

            if (modal) {
                options.popupRoot = modal;
            }

            return options;
        }

        window.redminInitEditors = function (root) {
            root = root || document;

            root.querySelectorAll('textarea').forEach(function (textarea) {
                // Jodit's own source-mode mirror textarea lives inside the editor container; never make an editor of it
                if (textarea.classList.contains('jodit-source__mirror') || textarea.closest('.jodit-container')) {
                    return;
                }

                // Jodit marks the source textarea with a non-enumerable `component` (Jodit.isJoditAssigned)
                if (Jodit.isJoditAssigned(textarea)) {
                    return;
                }

                editors.push(Jodit.make(textarea, optionsFor(textarea, root)));
            });
        };

        window.redminDestroyEditors = function (root) {
            root = root || document;

            root.querySelectorAll('textarea').forEach(function (textarea) {
                if (!Jodit.isJoditAssigned(textarea)) {
                    return;
                }

                var editor = textarea.component;
                var index = editors.indexOf(editor);

                if (index !== -1) {
                    editors.splice(index, 1);
                }

                editor.destruct();
            });
        };

        // Jodit's sticky toolbar keeps the width it had when it stuck; follow the layout when the sidebar collapses.
        window.addEventListener('resize', function () {
            editors.forEach(function (editor) {
                var container = editor.container;

                if (!container || !container.classList.contains('jodit_sticky')) {
                    return;
                }

                var toolbar = container.querySelector('.jodit-toolbar__box');

                if (toolbar) {
                    toolbar.style.width = (container.offsetWidth - 2) + 'px';
                }
            });
        });

        window.redminInitEditors(document);
    }();
</script>
