<script src="//tinymce.cachefly.net/4.0/tinymce.min.js"></script>
<script>
    !function ($) {
        $(function(){
            tinymce.init({
                selector:'textarea',
                menubar:false,
                plugins: "link image code",
                convert_urls: false,
                relative_urls: false,
                toolbar: "undo redo | formatselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link | image | code"
            });
        })
    }(window.jQuery);
</script>
