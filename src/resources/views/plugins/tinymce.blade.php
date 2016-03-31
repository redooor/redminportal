{{--
    Input form template for reuse
    --------------------------------
    Usage Example:
    --------------------------------
    @include('redminportal::plugins/tinymce', [
        'tinyImages' => 'optional images for insertion'
    ])
    --------------------------------
--}}
<script src="{{ URL::to('vendor/redooor/redminportal/js/tinymce/tinymce.min.js') }}"></script>
<script>
    !function ($) {
        $(function(){
            tinymce.init({
                skin: 'redooor',
                selector:'textarea',
                menubar:false,
                plugins: "link image code paste",
                convert_urls: false,
                relative_urls: false,
                paste_remove_styles_if_webkit: false,
                toolbar: "undo redo | formatselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link | image | code",
                body_class: 'container-fluid',
                content_css: [
                @foreach (config('redminportal::tinymce') as $tinymce_css)
                    "{{ url($tinymce_css) }}",
                @endforeach
                ],
                image_list: [
                @if (isset($tinyImages) and count($tinyImages) > 0)
                    @foreach ($tinyImages as $tinyImage)
                        {
                            title: '{{ pathinfo($tinyImage->path)["filename"] }}',
                            value: '{{ url(Redminportal::imagine()->getUrl($tinyImage->path, "large")) }}'
                        }
                    @endforeach
                @endif
                ]
            });
        })
    }(window.jQuery);
</script>
