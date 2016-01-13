<script src="{{ url('vendor/redooor/redminportal/js/typeahead.bundle.min.js') }}"></script>
<script src="{{ url('vendor/redooor/redminportal/js/bootstrap-tagsinput.min.js') }}"></script>
<script>
    ! function ($) {
        $(function () {
            // Prevent keypress Enter on Tag input to submit form
            $(document).on("keypress", ".bootstrap-tagsinput input", function (event) {
                if (event.keyCode == 13) {
                    event.preventDefault();
                }
            });
            // Add focus class to parent when focus in
            $(document).on("focusin", ".bootstrap-tagsinput input", function () {
                $('.bootstrap-tagsinput').addClass('tagsinput-focus');
            });
            // Remove focus class to parent when focus out
            $(document).on("focusout", ".bootstrap-tagsinput input", function () {
                $('.bootstrap-tagsinput').removeClass('tagsinput-focus');
            });
            var tagnames = new Bloodhound({
                datumTokenizer: Bloodhound.tokenizers.obj.whitespace('name'),
                queryTokenizer: Bloodhound.tokenizers.whitespace,
                prefetch: {
                    url: "{{ url('api/tag/name') }}",
                    cache: false,
                    filter: function (list) {
                        return $.map(list, function (tagname) {
                            return {
                                name: tagname
                            };
                        });
                    }
                }
            });
            tagnames.initialize();

            $('#tags, .tagsinput').tagsinput({
                freeInput: true,
                typeaheadjs: {
                    name: 'tagnames',
                    displayKey: 'name',
                    valueKey: 'name',
                    source: tagnames.ttAdapter()
                }
            });
        })
    }(window.jQuery);
</script>