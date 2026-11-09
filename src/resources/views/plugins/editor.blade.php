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
--}}
<link rel="stylesheet" href="{{ URL::to('vendor/redooor/redminportal/css/jodit/jodit.min.css') }}">
<script src="{{ URL::to('vendor/redooor/redminportal/js/jodit/jodit.min.js') }}"></script>
<script>
    !function () {
        var images = [
        @foreach (($editorImages ?? $tinyImages ?? []) as $editorImage)
            {
                title: @json(pathinfo($editorImage->path)['filename']),
                value: @json(url(Redminportal::imagine()->getUrl($editorImage->path, 'large')))
            },
        @endforeach
        ];

        // Insert an image uploaded to this record (the "media library" of the record being edited)
        Jodit.defaultOptions.controls.media = {
            icon: 'folder',
            tooltip: 'Insert uploaded image',
            isDisabled: function () { return images.length === 0; },
            popup: function (editor, current, close) {
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

        document.querySelectorAll('textarea').forEach(function (textarea) {
            editors.push(Jodit.make(textarea, {
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
                cleanHTML: { collapseEmptyValueToEmptyString: true }
            }));
        });

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
    }();
</script>
