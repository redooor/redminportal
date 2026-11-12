@extends('redminportal::layouts.master')

@section('navbar-breadcrumb')
    <li class="nav-item"><a class="nav-link" href="{{ URL::to('admin/announcements') }}">{{ Lang::get('redminportal::menus.announcements') }}</a></li>
    <li class="nav-item"><span class="nav-link active">{{ Lang::get('redminportal::forms.edit') }}</span></li>
@stop

@section('content')
    @include('redminportal::partials.confirm-delete')
    
    @include('redminportal::partials.errors')

    <form method="POST" action="{{ url('admin/announcements/store') }}" accept-charset="UTF-8" role="form" enctype="multipart/form-data">
        @csrf
        <input name="id" type="hidden" value="{{ $announcement->id }}">

    	<div class='row'>
    	    <div class="col-lg-3 order-lg-last">
                <div class="card card-body bg-body-tertiary">
                    <div class='form-actions'>
                        <a href="{{ url('admin/announcements') }}" class="btn btn-link btn-sm">{{ Lang::get('redminportal::buttons.cancel') }}</a>
                        <input class="btn btn-primary btn-sm float-end" type="submit" value="{{ Lang::get('redminportal::buttons.save') }}">
                    </div>
                </div>
                <div class='card card-body bg-body-tertiary p-2'>
                    <div class="mb-3">
                        <div class="form-check">
                            <label class="form-check-label" for="private-checker">
                                <input class="form-check-input" id="private-checker" name="private" type="checkbox" value="{{ $announcement->private }}"@if($announcement->private) checked="checked"@endif> {{ Lang::get('redminportal::forms.private') }}
                            </label>
                        </div>
                    </div>
                </div>
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
    	    <div class="col-lg-9 order-lg-first">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0">{{ Lang::get('redminportal::forms.edit_announcement') }}</h4>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="title">{{ Lang::get('redminportal::forms.title') }}</label>
                            <input class="form-control" name="title" type="text" id="title" value="{{ $announcement->title }}">
                        </div>
                        <div class="mb-3">
                            <label for="content">{{ Lang::get('redminportal::forms.content') }}</label>
                            <textarea class="form-control" name="content" rows="15" id="content">{{ $announcement->content }}</textarea>
                        </div>
                    </div>
                </div>
                @if (count($announcement->images) > 0)
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0">{{ Lang::get('redminportal::forms.uploaded_photos') }}</h4>
                    </div>
                    <div class="card-body">
                        <div class='row'>
                            @foreach( $announcement->images as $image )
                            <div class='col-lg-3'>
                                <img src="{{ url($imagine->getUrl($image->path)) }}" class="img-thumbnail" alt="{{ $announcement->name }}"> 
                                <br><br>
                                <div class="btn-group btn-group-sm">
                                    @permitted('admin/announcements/imgremove/' . $image->id)
                                    <a href="{{ URL::to('admin/announcements/imgremove/' . $image->id) }}" class="btn btn-danger btn-confirm">
                                        <i class="fa-solid fa-xmark"></i>
                                    </a>
                                    @endpermitted
                                    <a href="{{ URL::to($imagine->getUrl($image->path, 'large')) }}" class="btn btn-primary btn-copy">
                                        <i class="fa-solid fa-link"></i>
                                    </a>
                                    <a href="{{ URL::to($imagine->getUrl($image->path, 'large')) }}" class="btn btn-info" target="_blank" data-lightbox="uploaded-images" data-lightbox-title="{{ $image->path }}" aria-label="{{ Lang::get('redminportal::buttons.view_image') }}">
                                        <i class="fa-solid fa-eye"></i>
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
    <script src="{{ URL::to('vendor/redooor/redminportal/js/redmin-fileinput.min.js') }}"></script>
    @include('redminportal::plugins/editor', ['editorImages' => $announcement->images])
@stop
