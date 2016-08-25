@extends('redminportal::layouts.master')

@section('navbar-breadcrumb')
    <li><a href="{{ URL::to('admin/announcements') }}">{{ Lang::get('redminportal::menus.announcements') }}</a></li>
    <li class="active"><span class="navbar-text">{{ Lang::get('redminportal::forms.create') }}</span></li>
@stop

@section('content')
    
    @include('redminportal::partials.errors')

    <form method="POST" action="{{ url('admin/announcements/store') }}" accept-charset="UTF-8" role="form" enctype="multipart/form-data">
        {{ csrf_field() }}
        
    	<div class='row'>
            <div class="col-md-3 col-md-push-9">
                <div class="well">
                    <div class='form-actions'>
                        <a href="{{ url('admin/announcements') }}" class="btn btn-link btn-sm">{{ Lang::get('redminportal::buttons.cancel') }}</a>
                        <input class="btn btn-primary btn-sm pull-right" type="submit" value="{{ Lang::get('redminportal::buttons.create') }}">
                    </div>
                </div>
                <div class='well well-sm'>
                    <div class="form-group">
                        <div class="checkbox">
                            <label for="private-checker">
                                <input id="private-checker" checked="checked" name="private" type="checkbox" value="1"> {{ Lang::get('redminportal::forms.private') }}
                            </label>
                        </div>
                    </div>
                </div>
                <div>
                    <div class="fileupload fileupload-new" data-provides="fileupload">
                      <div class="fileupload-preview thumbnail" style="width: 200px; height: 150px;"></div>
                      <div>
                        <span class="btn btn-default btn-file"><span class="fileupload-new">{{ Lang::get('redminportal::forms.select_image') }}</span><span class="fileupload-exists">{{ Lang::get('redminportal::forms.change_image') }}</span><input name="image" type="file"></span>
                        <a href="#" class="btn btn-danger fileupload-exists" data-dismiss="fileupload">{{ Lang::get('redminportal::forms.remove_image') }}</a>
                      </div>
                    </div>
                </div>
            </div>
            <div class="col-md-9 col-md-pull-3">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h4 class="panel-title">{{ Lang::get('redminportal::forms.create_announcement') }}</h4>
                    </div>
                    <div class="panel-body">
                        <div class="form-group">
                            <label for="title">{{ Lang::get('redminportal::forms.title') }}</label>
                            <input class="form-control" name="title" type="text" id="title">
                        </div>
                        <div class="form-group">
                            <label for="content">{{ Lang::get('redminportal::forms.content') }}</label>
                            <textarea class="form-control" name="content" rows="15" id="content"></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
@stop

@section('footer')
    <script src="{{ URL::to('vendor/redooor/redminportal/js/bootstrap-fileupload.js') }}"></script>
    @include('redminportal::plugins/tinymce')
@stop
