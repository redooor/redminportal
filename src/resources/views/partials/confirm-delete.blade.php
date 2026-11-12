{{--
    Delete confirmation modal, only rendered on pages that include it
    --------------------------------
    Include once in any view that has a `.btn-confirm` delete link; repeated includes on one page are ignored:
    @include('redminportal::partials.confirm-delete')
--}}
@once
    @push('modals')
        <!-- Modal confirmation window -->
        {!! Redminportal::html()->modalWindow(
            'confirm-modal',
            Lang::get('redminportal::messages.confirm_delete'),
            Lang::get('redminportal::messages.are_you_sure_you_want_to_delete'),
            '<div class="d-flex justify-content-between w-100"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">' . Lang::get('redminportal::buttons.delete_no') . '</button><a href="#" id="confirm-modal-proceed" class="btn btn-danger" data-method="post">' . Lang::get('redminportal::buttons.delete_yes') . '</a></div>'
        ) !!}
        <!-- End of modal window -->
    @endpush

    @push('scripts')
        <script>
            !function ($) {
                $(function(){
                    $(document).on('click', '.btn-confirm', function(e) {
                        e.preventDefault();
                        $delete_url = $(this).attr('href');
                        $('#confirm-modal-proceed').attr('href', $delete_url);
                        bootstrap.Modal.getOrCreateInstance(document.getElementById('confirm-modal')).show();
                    });
                })
            }(window.jQuery);
        </script>
    @endpush
@endonce
