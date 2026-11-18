@extends('redminportal::layouts.master')

@section('navbar-breadcrumb')
    <li class="nav-item"><a class="nav-link" href="{{ URL::to('admin/pages') }}">{{ Lang::get('redminportal::menus.pages') }}</a></li>
    <li class="nav-item"><span class="nav-link active">{{ Lang::get('redminportal::forms.create') }}</span></li>
@stop

@section('content')
    
    @include('redminportal::partials.errors')

    <form method="POST" action="{{ action('\Redooor\Redminportal\App\Http\Controllers\PageController@postStore') }}" enctype="multipart/form-data" role="form">
    @csrf

    <div class='row'>
        <div class="col-lg-3 order-lg-last">
            <div class="card card-body bg-body-tertiary">
                <div class='form-actions'>
                    <a href="{{ URL::to('admin/pages') }}" class="btn btn-link btn-sm">{{ Lang::get('redminportal::buttons.cancel') }}</a>
                    <input type="submit" value="{{ Lang::get('redminportal::buttons.create') }}" class="btn btn-primary btn-sm float-end">
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
            {{-- Load Select Category partial form --}}
            @include('redminportal::partials.form-select-category', [
                'select_category_selected_name' => 'category_id',
                'select_category_selected_id' => '0',
                'select_category_categories' => $categories
            ])
            {{-- Load Tag form --}}
            {!! Redminportal::form()->tagger() !!}
            <div>
                @include('redminportal::partials.image-input')
            </div>
        </div>

        <div class="col-lg-9 order-lg-first">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">{{ Lang::get('redminportal::forms.create_page') }}</h4>
                </div>
                <div class="card-body">
                    <ul class="nav nav-tabs" id="lang-selector">
                       @foreach(\Config::get('redminportal::translation') as $translation)
                       <li class="nav-item"><a class="nav-link @if ($loop->first)active @endif" href="#lang-{{ $translation['lang'] }}" data-bs-toggle="tab" data-bs-target="#lang-{{ $translation['lang'] }}">{{ $translation['name'] }}</a></li>
                       @endforeach
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="lang-en">
                            <div class="mb-3">
                                <label for="title">{{ Lang::get('redminportal::forms.title') }}</label>
                                <input type="text" name="title" id="title" value="{{ old('title', null) }}" class="form-control">
                            </div>

                            <div class="mb-3">
                                <label for="slug">{{ Lang::get('redminportal::forms.slug') }}</label>
                                <input type="text" name="slug" id="slug" value="{{ old('slug', null) }}" class="form-control">
                            </div>

                            <div class="mb-3">
                                <label for="content">{{ Lang::get('redminportal::forms.content') }}</label>
                                <textarea name="content" id="content" class="form-control" style="height:400px">{{ old('content', null) }}</textarea>
                            </div>
                        </div>
                        @foreach(\Config::get('redminportal::translation') as $translation)
                            @if($translation['lang'] != 'en')
                            <div class="tab-pane fade" id="lang-{{ $translation['lang'] }}">
                                <div class="mb-3">
                                    <label for="{{ $translation['lang'] . '_title' }}">{{ Lang::get('redminportal::forms.title') }}</label>
                                    <input type="text" name="{{ $translation['lang'] . '_title' }}" id="{{ $translation['lang'] . '_title' }}" value="{{ old($translation['lang'] . '_title', null) }}" class="form-control">
                                </div>

                                <div class="mb-3">
                                    <label for="{{ $translation['lang'] . '_slug' }}">{{ Lang::get('redminportal::forms.slug') }}</label>
                                    <input type="text" name="{{ $translation['lang'] . '_slug' }}" id="{{ $translation['lang'] . '_slug' }}" value="{{ old($translation['lang'] . '_slug', null) }}" class="form-control">
                                </div>

                                <div class="mb-3">
                                    <label for="{{ $translation['lang'] . '_content' }}">{{ Lang::get('redminportal::forms.content') }}</label>
                                    <textarea name="{{ $translation['lang'] . '_content' }}" id="{{ $translation['lang'] . '_content' }}" class="form-control" style="height:400px">{{ old($translation['lang'] . '_content', null) }}</textarea>
                                </div>
                            </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    </form>
@stop

@section('footer')
    @parent
    <script src="{{ URL::to('vendor/redooor/redminportal/js/redmin-fileinput.min.js') }}"></script>
    <script>
        !function ($) {
            $(function(){
            })
        }(window.jQuery);
    </script>
    @include('redminportal::plugins/editor')
@stop
