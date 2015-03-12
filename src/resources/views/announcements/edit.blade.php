@extends('redminportal::layouts.master')

@section('content')
    @if($errors->has())
    <div class='alert alert-danger'>
        We encountered the following errors:
        <ul>
            @foreach($errors->all() as $message)
            <li>{{ $message }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    {{ Form::open(array('files' => TRUE, 'action' => 'Redooor\Redminportal\AnnouncementController@postStore', 'role' => 'form')) }}
        {{ Form::hidden('id', $announcement->id)}}

    	<div class='row'>
    	    <div class="col-md-3 col-md-push-9">
                <div class='form-actions text-right'>
                    {{ HTML::link('admin/announcements', 'Cancel', array('class' => 'btn btn-default'))}}
                    {{ Form::submit('Save Changes', array('class' => 'btn btn-primary')) }}
                </div>
                <hr>
                <div class='well well-sm'>
                    <div class="form-group">
                        <div class="checkbox">
                            <label for="private-checker">
                                {{ Form::checkbox('private', $announcement->private, $announcement->private, array('id' => 'private-checker')) }} Private
                            </label>
                        </div>
                    </div>
                </div>
                <div class="fileupload fileupload-new" data-provides="fileupload">
                    <div class="fileupload-preview thumbnail" style="width: 200px; height: 150px;"></div>
                    <div>
                        <span class="btn btn-default btn-file"><span class="fileupload-new">Upload photo</span><span class="fileupload-exists">Change</span>{{ Form::file('image') }}</span>
                        <a href="#" class="btn btn-danger fileupload-exists" data-dismiss="fileupload">Remove</a>
                    </div>
                </div>
            </div>
    	    <div class="col-md-9 col-md-pull-3">
                <div class="form-group">
                    {{ Form::label('title', 'Title') }}
                    {{ Form::text('title', $announcement->title, array('class' => 'form-control')) }}
                </div>
                <div class="form-group">
                    {{ Form::label('content', 'Content') }}
                    {{ Form::textarea('content', $announcement->content, array('class' => 'form-control')) }}
                </div>
                <h4>Uploaded Photos</h4>
                <div class='row'>
                    @foreach( $announcement->images as $image )
                    <div class='col-md-3'>
                    	{{ HTML::image($imagine->getUrl($image->path), $announcement->name, array('class' => 'img-thumbnail', 'alt' => $image->path)) }}
                        <br><br>
                        <div class="btn-group btn-group-sm">
                            <a href="{{ URL::to('admin/announcements/imgremove/' . $image->id) }}" class="btn btn-danger btn-confirm">
                                <span class="glyphicon glyphicon-remove"></span>
                            </a>
                            <a href="{{ URL::to($imagine->getUrl($image->path, 'large')) }}" class="btn btn-primary btn-copy">
                                <span class="glyphicon glyphicon-link"></span>
                            </a>
                            <a href="{{ URL::to($imagine->getUrl($image->path, 'large')) }}" class="btn btn-info" target="_blank">
                                <span class="glyphicon glyphicon-eye-open"></span>
                            </a>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
    	</div>

        <hr>


    {{ Form::close() }}
@stop

@section('footer')
    <script src="{{ URL::to('packages/redooor/redminportal/assets/js/bootstrap-fileupload.js') }}"></script>
    @include('redminportal::plugins/tinymce')
@stop
