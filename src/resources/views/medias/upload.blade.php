@extends('redminportal::layouts.master')

@section('navbar-breadcrumb')
    <li class="nav-item"><a class="nav-link" href="{{ URL::to('admin/medias') }}">{{ Lang::get('redminportal::menus.medias') }}</a></li>
    <li class="nav-item"><a class="nav-link" href="{{ URL::to('admin/medias/edit/' . $model->id) }}">{{ Lang::get('redminportal::forms.edit') }}</a></li>
    <li class="nav-item"><span class="nav-link active">{{ Lang::get('redminportal::forms.upload') }}</span></li>
@stop

@section('content')

    <div id="console" class="alert alert-danger" style="display:none;"></div>
    
    <div class='row'>
        <div class="col-lg-3 order-lg-last">
            <div class="card card-body bg-body-tertiary">
                <div class='form-actions'>
                    {!! HTML::link('admin/medias', Lang::get('redminportal::buttons.cancel'), array('class' => 'btn btn-link btn-sm'))!!}
                    <a id="start-upload" class="btn btn-primary btn-sm float-end" href="javascript:;" data-src="{{ URL::to('admin/medias/upload') . '/' . $model->id }}">{{ Lang::get('redminportal::buttons.upload') }}</a>
                </div>
            </div>
            <input type="hidden" id="_token" name="_token" value="{{ csrf_token() }}">
        </div>
        <div class="col-lg-9 order-lg-first">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">{{ $model->name }}</h4>
                </div>
                <div class="card-body">
                    <ul id="filelist"></ul>
                    <br>
                    <a id="browse" class="btn btn-primary btn-sm" href="javascript:;">{{ Lang::get('redminportal::forms.browse_file') }}</a>
                </div>
                <div class="card-footer">
                    {{ Lang::get('redminportal::forms.existing_file') }}: <strong>{{ $model->path }}</strong> [{{ $model->mimetype }}]
                </div>
            </div>
        </div>
    </div>
@stop

@section('footer')
    <script type="text/javascript" src="{{ URL::to('vendor/redooor/redminportal/js/plupload.full.min.js') }}"></script>
    <script type="text/javascript">
        jQuery( document ).ready(function( $ ) {
            var token = $('meta[name="csrf-token"]').attr('content') || $('#_token').val();
            
            var uploader = new plupload.Uploader({
                browse_button: 'browse', // this can be an id of a DOM element or the DOM element itself
                url: $('#start-upload').attr('data-src'),
                chunk_size: '200kb',
                max_retries: 3,
                multi_selection: false, // Disallow multiple files
                headers: { 'X-CSRF-TOKEN': token },
                filters: { prevent_duplicates: true }
            });

            uploader.init();

            uploader.bind('FilesAdded', function(up, files) {
                var html = '';
                $('#console').hide();
                
                // Allow only 1 file upload
                while (up.files.length > 1) {
                    up.removeFile(up.files[0]);
                }
                
                plupload.each(up.files, function(file) {
                    html += '<li id="' + file.id + '">' + $('<span>').text(file.name).html() + ' (' + plupload.formatSize(file.size) + ') <b><a href="#" class="link-del">delete</a></b></li>';
                });
                document.getElementById('filelist').innerHTML = html;
            });

            uploader.bind('UploadProgress', function(up, file) {
                var html = '';
                if (file.percent == 100) {
                    html = '<span>' + file.percent + '%</span> <span class="badge text-bg-success">Upload Completed</span>';
                } else {
                    html = '<span>' + file.percent + "%</span>";
                }
                document.getElementById(file.id).getElementsByTagName('b')[0].innerHTML = html;
            });

            uploader.bind('Error', function(up, err) {
                document.getElementById('console').innerHTML = "Error #" + err.code + ": " + err.message;
                $('#console').removeClass('alert-success').addClass('alert-danger').show();
            });
            
            // Check error after complete
            uploader.bind('FileUploaded', function(up, file, info) {
                var response = jQuery.parseJSON(info.response);
                if (response.code == 0) {
                    document.getElementById('console').innerHTML = "Error #" + response.code + ": " + response.message;
                    $('#console').removeClass('alert-success').addClass('alert-danger').show();
                } else {
                    document.getElementById('console').innerHTML = "Success #" + response.code + ": " + response.message;
                    $('#console').removeClass('alert-danger').addClass('alert-success').show();
                }
            });

            document.getElementById('start-upload').onclick = function() {
                $('#console').hide();
                uploader.start();
            };

            $(document).on('click', '.link-del', function(e) {
                e.preventDefault();
                $('#console').hide();
                $id = $(this).parents('li').attr('id');
                uploader.removeFile(uploader.getFile($id));
                $(this).parents('li').remove();
            });
        });

    </script>
@stop
