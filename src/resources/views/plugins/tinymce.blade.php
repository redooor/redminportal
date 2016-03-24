<script src="{{ URL::to('vendor/redooor/redminportal/js/tinymce/tinymce.min.js') }}"></script>
<script>
    !function ($) {
        $(function(){
            tinymce.init({
                skin: 'redooor',
                selector:'textarea',
                menubar:false,
                plugins: "link image code",
                convert_urls: false,
                relative_urls: false,
                toolbar: "undo redo | formatselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link | image | code",
                body_class: 'container-fluid',
                content_css: [
                @foreach (config('redminportal::tinymce') as $tinymce_css)
                    "{{ url($tinymce_css) }}",
                @endforeach
                ]
            });
        })
    }(window.jQuery);
</script>
