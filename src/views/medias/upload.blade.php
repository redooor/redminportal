@extends('redminportal::layouts.master')

@section('content')
    <pre id="console" style="display:none;"></pre>
    <br />

    <div id="container">
        <a class="btn btn-default pull-right" href="{{ URL::to('admin/medias') }}">Back</a>
        <a id="browse" class="btn btn-default" href="javascript:;">Browse...</a>
        <a id="start-upload" class="btn btn-primary" href="javascript:;" data-src="{{ URL::to('admin/medias/upload') . '/' . $media->id }}">Start Upload</a>
    </div>

    <br />
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">{{ $media->name }}</h3>
        </div>
        <div class="panel-body">
            <ul id="filelist"></ul>
        </div>
        <div class="panel-footer">
            Existing file: <strong>{{ $media->path }}</strong> [{{ $media->mimetype }}]
        </div>
    </div>
@stop

@section('footer')
    <script type="text/javascript" src="{{ URL::to('packages/redooor/redminportal/assets/js/plupload.full.min.js') }}"></script>
    <script type="text/javascript">
        jQuery( document ).ready(function( $ ) {
            var uploader = new plupload.Uploader({
                browse_button: 'browse', // this can be an id of a DOM element or the DOM element itself
                url: $('#start-upload').attr('data-src'),
                chunk_size: '200kb',
                max_retries: 3
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
