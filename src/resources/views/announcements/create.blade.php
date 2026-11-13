@extends('redminportal::layouts.master')

@section('navbar-breadcrumb')
    <li class="nav-item"><a class="nav-link" href="{{ URL::to('admin/announcements') }}">{{ Lang::get('redminportal::menus.announcements') }}</a></li>
    <li class="nav-item"><span class="nav-link active">{{ Lang::get('redminportal::forms.create') }}</span></li>
@stop

@section('content')
    
    @include('redminportal::partials.errors')

    <form method="POST" action="{{ url('admin/announcements/store') }}" accept-charset="UTF-8" role="form" enctype="multipart/form-data">
        @csrf
        
    	<div class='row'>
            <div class="col-lg-3 order-lg-last">
                <div class="card card-body bg-body-tertiary">
                    <div class='form-actions'>
                        <a href="{{ url('admin/announcements') }}" class="btn btn-link btn-sm">{{ Lang::get('redminportal::buttons.cancel') }}</a>
                        <input class="btn btn-primary btn-sm float-end" type="submit" value="{{ Lang::get('redminportal::buttons.create') }}">
                    </div>
                </div>
                <div class='card card-body bg-body-tertiary p-2'>
                    <div class="mb-3">
                        <div class="form-check">
                            <label class="form-check-label" for="private-checker">
                                <input class="form-check-input" id="private-checker" checked="checked" name="private" type="checkbox" value="1"> {{ Lang::get('redminportal::forms.private') }}
                            </label>
                        </div>
                    </div>
                </div>
                <div>
                    <div class="redmin-fileinput" data-redmin-fileinput>
                        <div class="redmin-fileinput-preview img-thumbnail"></div>
                        <div>
                            <label class="btn btn-secondary">
                                <span class="redmin-fileinput-new">{{ Lang::get('redminportal::forms.select_image') }}</span><span class="redmin-fileinput-exists">{{ Lang::get('redminportal::forms.change_image') }}</span>
                                <input name="image" type="file" class="visually-hidden">
                            </label>
                            <button type="button" class="btn btn-danger redmin-fileinput-exists" data-redmin-fileinput-clear>{{ Lang::get('redminportal::forms.remove_image') }}</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-9 order-lg-first">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0">{{ Lang::get('redminportal::forms.create_announcement') }}</h4>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="title">{{ Lang::get('redminportal::forms.title') }}</label>
                            <input class="form-control" name="title" type="text" id="title">
                        </div>
                        <div class="mb-3">
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
    <script src="{{ URL::to('vendor/redooor/redminportal/js/redmin-fileinput.min.js') }}"></script>
    @include('redminportal::plugins/editor')
@stop
