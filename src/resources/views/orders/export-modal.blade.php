<div id="export-csv" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            {!! Form::open(array('action' => '\Redooor\Redminportal\App\Http\Controllers\ReportController@postOrders', 'report' => 'form')) !!}
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">{{ Lang::get('redminportal::buttons.close') }}</span></button>
                <h4 class="modal-title">{{ Lang::get('redminportal::messages.export_to_excel') }}</h4>
            </div>
            <div class="modal-body">
                <div class='row'>
                    <div class="col-md-12">
                        <div class="form-group">
                            {!! Form::label('start_date', Lang::get('redminportal::forms.start_date')) !!}
                            <div class="input-group" id='start-date'>
                                {!! Form::input('text', 'start_date', null, array('class' => 'form-control datepicker', 'readonly')) !!}
                                <span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>
                            </div>
                        </div>
                        <div class="form-group">
                            {!! Form::label('end_date', Lang::get('redminportal::forms.end_date')) !!}
                            <div class="input-group" id='end-date'>
                                {!! Form::input('text', 'end_date', null, array('class' => 'form-control datepicker', 'readonly')) !!}
                                <span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>
                            </div>
                        </div>
                        <p class="help-block">{{ Lang::get('redminportal::messages.leave_all_blank_to_download_all') }}</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">{{ Lang::get('redminportal::buttons.close') }}</button>
                {!! Form::submit(Lang::get('redminportal::buttons.download_excel'), array('class' => 'btn btn-primary')) !!}
            </div>
            {!! Form::close() !!}
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->