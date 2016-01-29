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
                $(this).parents('.bootstrap-tagsinput').addClass('tagsinput-focus');
            });
            // Remove focus class to parent when focus out
            $(document).on("focusout", ".bootstrap-tagsinput input", function () {
                $('.bootstrap-tagsinput.tagsinput-focus').removeClass('tagsinput-focus');
            });
            // Initiate bootstrap-tagsinput
            $('#permission-inherit').tagsinput({
                freeInput: true,
                trimValue: true,
                tagClass: 'label label-default'
            });
            $('#permission-allow').tagsinput({
                freeInput: true,
                trimValue: true,
                tagClass: 'label label-success'
            });
            $('#permission-deny').tagsinput({
                freeInput: true,
                trimValue: true,
                tagClass: 'label label-danger'
            });
            // -------------------
            // Permission Builder
            // -------------------
            $(document).on('click', '#btn-add-permission', function(e) {
                e.preventDefault();
                $selected_permission = $('#select-permission option:selected').val();
                $('#select-route option:selected').each(function() {
                    $selected_route = $(this).val();
                    $('#select-action option:selected').each(function() {
                        $selected_action = $(this).val();
                        $permission_string = $selected_route + '.' + $selected_action;
                        switch($selected_permission) {
                            case 0:
                            case '0':
                                // Add to this permission
                                $('#permission-inherit').tagsinput('add', $permission_string);
                                // Remove from all other permissions
                                $('#permission-allow').tagsinput('remove', $permission_string);
                                $('#permission-deny').tagsinput('remove', $permission_string);
                                break;
                            case 1:
                            case '1':
                                // Add to this permission
                                $('#permission-allow').tagsinput('add', $permission_string);
                                // Remove from all other permissions
                                $('#permission-inherit').tagsinput('remove', $permission_string);
                                $('#permission-deny').tagsinput('remove', $permission_string);
                                break;
                            case -1:
                            case '-1':
                                // Add to this permission
                                $('#permission-deny').tagsinput('add', $permission_string);
                                // Remove from all other permissions
                                $('#permission-inherit').tagsinput('remove', $permission_string);
                                $('#permission-allow').tagsinput('remove', $permission_string);
                                break;
                            default:
                                break;
                        }
                    });
                });
            });
        })
    }(window.jQuery);
</script>