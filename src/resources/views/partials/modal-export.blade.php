{{--
    Export Modal Window with date selector template for reuse
    --------------------------------
    Usage Example:
    --------------------------------
    @include('redminportal::partials.modal-export', [
        'export_id' => 'export-modal-unique-id',
        'export_title' => 'Export title',
        'export_url' => 'Export POST Url'
    ])
--}}
<div id="{{ $export_id }}" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ $export_url }}" accept-charset="UTF-8" report="form">
                {{ csrf_field() }}
                <div class="modal-header">
                    <h4 class="modal-title">{{ $export_title }}</h4>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ Lang::get('redminportal::buttons.close') }}"></button>
                </div>
                <div class="modal-body">
                    <div class='row'>
                        <div class="col-lg-12">
                            <div class="mb-3">
                                <label for="start_date">{{ Lang::get('redminportal::forms.start_date') }}</label>
                                <div class="input-group" id='start-date'>
                                    <input class="form-control datepicker" readonly="readonly" name="start_date" type="text" id="start_date">
                                    <span class="input-group-text"><i class="fa-solid fa-calendar"></i></span>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="end_date">{{ Lang::get('redminportal::forms.end_date') }}</label>
                                <div class="input-group" id='end-date'>
                                    <input class="form-control datepicker" readonly="readonly" name="end_date" type="text" id="end_date">
                                    <span class="input-group-text"><i class="fa-solid fa-calendar"></i></span>
                                </div>
                            </div>
                            <p class="form-text">{{ Lang::get('redminportal::messages.leave_all_blank_to_download_all') }}</p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ Lang::get('redminportal::buttons.close') }}</button>
                    <input class="btn btn-primary" type="submit" value="{{ $export_title }}">
                </div>
            </form>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->