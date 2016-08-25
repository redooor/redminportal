@extends('redminportal::layouts.master')

@section('navbar-breadcrumb')
    <li><a href="{{ URL::to('admin/announcements') }}">{{ Lang::get('redminportal::menus.announcements') }}</a></li>
    <li class="active"><span class="navbar-text">{{ Lang::get('redminportal::forms.edit') }}</span></li>
@stop

@section('content')
    
    @include('redminportal::partials.errors')

    <form method="POST" action="{{ url('admin/announcements/store') }}" accept-charset="UTF-8" role="form" enctype="multipart/form-data">
        <input type="hidden" name="_token" value="{{ csrf_token() }}">
        <input name="id" type="hidden" value="{{ $announcement->id }}">

    	<div class='row'>
    	    <div class="col-md-3 col-md-push-9">
                <div class="well">
                    <div class='form-actions'>
                        <a href="{{ url('admin/announcements') }}" class="btn btn-link btn-sm">{{ Lang::get('redminportal::buttons.cancel') }}</a>
                        <input class="btn btn-primary btn-sm pull-right" type="submit" value="{{ Lang::get('redminportal::buttons.save') }}">
                    </div>
                </div>
                <div class='well well-sm'>
                    <div class="form-group">
                        <div class="checkbox">
                            <label for="private-checker">
                                <input id="private-checker" name="private" type="checkbox" value="{{ $announcement->private }}"@if($announcement->private) checked="checked"@endif> {{ Lang::get('redminportal::forms.private') }}
                            </label>
                        </div>
                    </div>
                </div>
                <div class="fileupload fileupload-new" data-provides="fileupload">
                    <div class="fileupload-preview thumbnail" style="width: 200px; height: 150px;"></div>
                    <div>
                        <span class="btn btn-default btn-file"><span class="fileupload-new">{{ Lang::get('redminportal::forms.select_image') }}</span><span class="fileupload-exists">{{ Lang::get('redminportal::forms.change_image') }}</span><input name="image" type="file"></span>
                        <a href="#" class="btn btn-danger fileupload-exists" data-dismiss="fileupload">{{ Lang::get('redminportal::forms.remove_image') }}</a>
                    </div>
                </div>
            </div>
    	    <div class="col-md-9 col-md-pull-3">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h4 class="panel-title">{{ Lang::get('redminportal::forms.edit_announcement') }}</h4>
                    </div>
                    <div class="panel-body">
                        <div class="form-group">
                            <label for="title">{{ Lang::get('redminportal::forms.title') }}</label>
                            <input class="form-control" name="title" type="text" id="title" value="{{ $announcement->title }}">
                        </div>
                        <div class="form-group">
                            <label for="content">{{ Lang::get('redminportal::forms.content') }}</label>
                            <textarea class="form-control" name="content" rows="15" id="content">{{ $announcement->content }}</textarea>
                        </div>
                    </div>
                </div>
                @if (count($announcement->images) > 0)
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h4 class="panel-title">{{ Lang::get('redminportal::forms.uploaded_photos') }}</h4>
                    </div>
                    <div class="panel-body">
                        <div class='row'>
                            @foreach( $announcement->images as $image )
                            <div class='col-md-3'>
                                <img src="{{ url($imagine->getUrl($image->path)) }}" class="img-thumbnail" alt="{{ $announcement->name }}"> 
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
                @endif
            </div>
    	</div>
    </form>
@stop

@section('footer')
    <script src="{{ URL::to('vendor/redooor/redminportal/js/bootstrap-fileupload.js') }}"></script>
    @include('redminportal::plugins/tinymce', ['tinyImages' => $announcement->images])
@stop
