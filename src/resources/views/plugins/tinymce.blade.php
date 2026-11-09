{{--
    Deprecated: the TinyMCE editor was replaced by Jodit. This wrapper keeps views that still
    include 'redminportal::plugins/tinymce' (for example published overrides) working.
    Include 'redminportal::plugins/editor' instead.
--}}
@include('redminportal::plugins/editor', ['editorImages' => $editorImages ?? $tinyImages ?? []])
