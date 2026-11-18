{{--
    Tags input (bootstrap-tagsinput + typeahead) for the page's tag fields
    --------------------------------
    Call window.redminInitTags(root) after injecting a fragment (an Element or document) and
    window.redminDestroyTags(root) before removing it. Existing pages keep #tags and .tagsinput;
    fragments can opt in with [data-redmin-tags].
--}}
<script src="{{ url('vendor/redooor/redminportal/js/typeahead.bundle.min.js') }}"></script>
<script src="{{ url('vendor/redooor/redminportal/js/bootstrap-tagsinput.min.js') }}"></script>
<script>
    ! function ($) {
        // One Bloodhound engine shared by every tags input on the page.
        var tagnames = null;

        function tagEngine() {
            if (!tagnames) {
                tagnames = new Bloodhound({
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
            }

            return tagnames;
        }

        function tagInputs(root) {
            return $(root).find('#tags, .tagsinput, [data-redmin-tags]');
        }

        // typeahead.js 0.11 builds the menu next to the input, so a widget initialised inside the
        // modal keeps its dropdown inside the modal; the bundled version has no menu-container option.
        window.redminInitTags = function (root) {
            tagInputs(root || document).filter(function () {
                return !$(this).data('tagsinput');
            }).tagsinput({
                freeInput: true,
                typeaheadjs: {
                    name: 'tagnames',
                    displayKey: 'name',
                    valueKey: 'name',
                    limit: 10,
                    source: tagEngine().ttAdapter()
                }
            });
        };

        window.redminDestroyTags = function (root) {
            tagInputs(root || document).filter(function () {
                return !!$(this).data('tagsinput');
            }).tagsinput('destroy');
        };

        $(function () {
            // Prevent keypress Enter on Tag input to submit form
            $(document).on("keypress", ".bootstrap-tagsinput input", function (event) {
                if (event.keyCode == 13) {
                    event.preventDefault();
                }
            });
            // Add focus class to the focused widget only, not to every tags input on the page
            $(document).on("focusin", ".bootstrap-tagsinput input", function () {
                $(this).closest('.bootstrap-tagsinput').addClass('tagsinput-focus');
            });
            // Remove focus class from the focused widget
            $(document).on("focusout", ".bootstrap-tagsinput input", function () {
                $(this).closest('.bootstrap-tagsinput').removeClass('tagsinput-focus');
            });

            window.redminInitTags(document);
        })
    }(window.jQuery);
</script>
