@extends('redminportal::layouts.master')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <ol class="breadcrumb">
                <li><a href="{{ URL::to('admin') }}">{{ Lang::get('redminportal::menus.home') }}</a></li>
                <li><a href="{{ URL::to('admin/announcements') }}">{{ Lang::get('redminportal::menus.announcements') }}</a></li>
                <li class="active">{{ Lang::get('redminportal::forms.create') }}</li>
            </ol>
        </div>
    </div>
    
    @include('redminportal::partials.errors')

    {!! Form::open(array('files' => TRUE, 'action' => '\Redooor\Redminportal\App\Http\Controllers\AnnouncementController@postStore', 'role' => 'form')) !!}

    	<div class='row'>
            <div class="col-md-3 col-md-push-9">
                <div class="well">
                    <div class='form-actions'>
                        {!! HTML::link('admin/announcements', Lang::get('redminportal::buttons.cancel'), array('class' => 'btn btn-link btn-sm'))!!}
                        {!! Form::submit(Lang::get('redminportal::buttons.create'), array('class' => 'btn btn-primary btn-sm pull-right')) !!}
                    </div>
                </div>
                <div class='well well-sm'>
                    <div class="form-group">
                        <div class="checkbox">
                            <label for="private-checker">
                                {!! Form::checkbox('private', true, true, array('id' => 'private-checker')) !!} {{ Lang::get('redminportal::forms.private') }}
                            </label>
                        </div>
                    </div>
                </div>
                <div>
                    <div class="fileupload fileupload-new" data-provides="fileupload">
                      <div class="fileupload-preview thumbnail" style="width: 200px; height: 150px;"></div>
                      <div>
                        <span class="btn btn-default btn-file"><span class="fileupload-new">{{ Lang::get('redminportal::forms.select_image') }}</span><span class="fileupload-exists">{{ Lang::get('redminportal::forms.change_image') }}</span>{!! Form::file('image') !!}</span>
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
                            {!! Form::label('title', Lang::get('redminportal::forms.title')) !!}
                            {!! Form::text('title', null, array('class' => 'form-control')) !!}
                        </div>
                        <div class="form-group">
                            {!! Form::label('content', Lang::get('redminportal::forms.content')) !!}
                            {!! Form::textarea('content', null, array('class' => 'form-control')) !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    {!! Form::close() !!}
@stop

@section('footer')
    <script src="{{ URL::to('vendor/redooor/redminportal/js/bootstrap-fileupload.js') }}"></script>
    @include('redminportal::plugins/tinymce')
@stop
