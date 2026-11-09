@extends('redminportal::layouts.master')

@section('navbar-breadcrumb')
    <li class="nav-item"><a class="nav-link" href="{{ URL::to('admin/posts') }}">{{ Lang::get('redminportal::menus.posts') }}</a></li>
    <li class="nav-item"><span class="nav-link active">{{ Lang::get('redminportal::forms.edit') }}</span></li>
@stop

@section('content')
    
    @include('redminportal::partials.errors')

    {!! Form::open(array('files' => TRUE, 'action' => '\Redooor\Redminportal\App\Http\Controllers\PostController@postStore', 'role' => 'form')) !!}
        {!! Form::hidden('id', $post->id) !!}

    	<div class='row'>
            <div class="col-lg-3 order-lg-last">
                <div class="card card-body bg-body-tertiary">
                    <div class='form-actions'>
                        {!! HTML::link('admin/posts', Lang::get('redminportal::buttons.cancel'), array('class' => 'btn btn-link btn-sm'))!!}
                        {!! Form::submit(Lang::get('redminportal::buttons.save'), array('class' => 'btn btn-primary btn-sm float-end')) !!}
                    </div>
                </div>
                <div class='card card-body bg-body-tertiary p-2'>
                    <div class="mb-3">
                        <div class="form-check">
                            <label class="form-check-label" for="featured-checker">
                                {!! Form::checkbox('featured', $post->featured, $post->featured, array('class' => 'form-check-input', 'id' => 'featured-checker')) !!} {{ Lang::get('redminportal::forms.featured') }}
                            </label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <label class="form-check-label" for="private-checker">
                                {!! Form::checkbox('private', $post->private, $post->private, array('class' => 'form-check-input', 'id' => 'private-checker')) !!} {{ Lang::get('redminportal::forms.private') }}
                            </label>
                        </div>
                    </div>
                </div>
                {{-- Load Select Category partial form --}}
                @include('redminportal::partials.form-select-category', [
                    'select_category_selected_name' => 'category_id',
                    'select_category_selected_id' => $post->category_id,
                    'select_category_categories' => $categories
                ])
                {{-- Load Tag form --}}
                {!! Redminportal::form()->tagger($tagString) !!}
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
                        <h4 class="card-title mb-0">{{ Lang::get('redminportal::forms.edit_post') }}</h4>
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
                                    {!! Form::label('title', Lang::get('redminportal::forms.title')) !!}
                                    {!! Form::text('title', $post->title, array('class' => 'form-control')) !!}
                                </div>

                                <div class="mb-3">
                                    {!! Form::label('slug', Lang::get('redminportal::forms.slug')) !!}
                                    {!! Form::text('slug', $post->slug, array('class' => 'form-control')) !!}
                                </div>

                                <div class="mb-3">
                                    {!! Form::label('content', Lang::get('redminportal::forms.content')) !!}
                                    {!! Form::textarea('content', $post->content, array('class' => 'form-control', 'style' => 'height:400px')) !!}
                                </div>
                            </div>
                            @foreach(\Config::get('redminportal::translation') as $translation)
                                @if($translation['lang'] != 'en')
                                <div class="tab-pane fade" id="lang-{{ $translation['lang'] }}">
                                    <div class="mb-3">
                                        {!! Form::label($translation['lang'] . '_title', Lang::get('redminportal::forms.title')) !!}
                                        @if ($translated)
                                        {!! Form::text($translation['lang'] . '_title', (array_key_exists($translation['lang'], $translated) ? $translated[$translation['lang']]->title : ''), array('class' => 'form-control')) !!}
                                        @else
                                        {!! Form::text($translation['lang'] . '_title', null, array('class' => 'form-control')) !!}
                                        @endif
                                    </div>

                                    <div class="mb-3">
                                        {!! Form::label($translation['lang'] . '_slug', Lang::get('redminportal::forms.slug')) !!}
                                        @if ($translated)
                                        {!! Form::text($translation['lang'] . '_slug', (array_key_exists($translation['lang'], $translated) ? $translated[$translation['lang']]->slug : ''), array('class' => 'form-control')) !!}
                                        @else
                                        {!! Form::text($translation['lang'] . '_slug', null, array('class' => 'form-control')) !!}
                                        @endif
                                    </div>

                                    <div class="mb-3">
                                        {!! Form::label($translation['lang'] . '_content', Lang::get('redminportal::forms.content')) !!}
                                        @if ($translated)
                                        {!! Form::textarea($translation['lang'] . '_content', (array_key_exists($translation['lang'], $translated) ? $translated[$translation['lang']]->content : ''), array('class' => 'form-control', 'style' => 'height:400px')) !!}
                                        @else
                                        {!! Form::textarea($translation['lang'] . '_content', null, array('class' => 'form-control', 'style' => 'height:400px')) !!}
                                        @endif
                                    </div>
                                </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
                @if (count($post->images) > 0)
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0">{{ Lang::get('redminportal::forms.uploaded_photos') }}</h4>
                    </div>
                    <div class="card-body">
                        <div class='row'>
                            @foreach ($post->images as $image)
                            <div class='col-lg-3'>
                                {!! HTML::image($imagine->getUrl($image->path), $post->title, array('class' => 'img-thumbnail', 'alt' => $image->path)) !!}
                                <br><br>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ URL::to('admin/posts/imgremove/' . $image->id) }}" class="btn btn-danger btn-confirm">
                                        <i class="fa-solid fa-xmark"></i>
                                    </a>
                                    <a href="{{ URL::to($imagine->getUrl($image->path, 'large')) }}" class="btn btn-primary btn-copy">
                                        <i class="fa-solid fa-link"></i>
                                    </a>
                                    <a href="{{ URL::to($imagine->getUrl($image->path, 'large')) }}" class="btn btn-info" target="_blank">
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
    {!! Form::close() !!}
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
    @include('redminportal::plugins/editor', ['editorImages' => $post->images])
@stop
