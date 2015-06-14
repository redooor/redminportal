@extends('redminportal::layouts.master')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <ol class="breadcrumb">
                <li><a href="{{ URL::to('admin') }}">{{ Lang::get('redminportal::menus.home') }}</a></li>
                <li><a href="{{ URL::to('admin/medias') }}">{{ Lang::get('redminportal::menus.medias') }}</a></li>
                <li><a href="{{ URL::to('admin/medias/edit/' . $media->id) }}">{{ Lang::get('redminportal::forms.edit') }}</a></li>
                <li class="active">{{ Lang::get('redminportal::forms.upload') }}</li>
            </ol>
        </div>
    </div>

    <pre id="console" style="display:none;"></pre>
    
    <div class='row'>
        <div class="col-md-3 col-md-push-9">
            <div class="well">
                <div class='form-actions'>
                    {!! HTML::link('admin/medias', Lang::get('redminportal::buttons.cancel'), array('class' => 'btn btn-link btn-sm'))!!}
                    <a id="start-upload" class="btn btn-primary btn-sm pull-right" href="javascript:;" data-src="{{ URL::to('admin/medias/upload') . '/' . $media->id }}">{{ Lang::get('redminportal::buttons.upload') }}</a>
                </div>
            </div>
            <input type="hidden" id="_token" name="_token" value="{{ \Crypt::encrypt(csrf_token()) }}">
        </div>
        <div class="col-md-9 col-md-pull-3">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4 class="panel-title">{{ $media->name }}</h4>
                </div>
                <div class="panel-body">
                    <ul id="filelist"></ul>
                    <br>
                    <a id="browse" class="btn btn-primary btn-sm" href="javascript:;">{{ Lang::get('redminportal::forms.browse_file') }}</a>
                </div>
                <div class="panel-footer">
                    {{ Lang::get('redminportal::forms.existing_file') }}: <strong>{{ $media->path }}</strong> [{{ $media->mimetype }}]
                </div>
            </div>
        </div>
    </div>
@stop

@section('footer')
    <script type="text/javascript" src="{{ URL::to('vendor/redooor/redminportal/js/plupload.full.min.js') }}"></script>
    <script type="text/javascript">
        jQuery( document ).ready(function( $ ) {
            var token = $('#_token').val();
            
            var uploader = new plupload.Uploader({
                browse_button: 'browse', // this can be an id of a DOM element or the DOM element itself
                url: $('#start-upload').attr('data-src'),
                chunk_size: '200kb',
                max_retries: 3,
                headers: { 'X-XSRF-TOKEN': token }
            });

            uploader.init();

            uploader.bind('FilesAdded', function(up, files) {
                var html = '';
                plupload.each(files, function(file) {
                    html += '<li id="' + file.id + '">' + file.name + ' (' + plupload.formatSize(file.size) + ') <b><a href="#" class="link-del">delete</a></b></li>';
                });
                document.getElementById('filelist').innerHTML += html;
            });

            uploader.bind('UploadProgress', function(up, file) {
                var html = '';
                if (file.percent == 100) {
                    html = '<span>' + file.percent + '%</span> <span class="label label-success">Upload Completed</span>';
                } else {
                    html = '<span>' + file.percent + "%</span>";
                }
                document.getElementById(file.id).getElementsByTagName('b')[0].innerHTML = html;
            });

            uploader.bind('Error', function(up, err) {
                document.getElementById('console').innerHTML += "\nError #" + err.code + ": " + err.message;
                $('#console').show();
            });

            document.getElementById('start-upload').onclick = function() {
                uploader.start();
            };

            $(document).on('click', '.link-del', function(e) {
                e.preventDefault();
                $id = $(this).parents('li').attr('id');
                uploader.removeFile(uploader.getFile($id));
                $(this).parents('li').remove();
            });
        });

    </script>
@stop
