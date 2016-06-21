@extends('redminportal::layouts.master')

@section('navbar-breadcrumb')
    <li><a href="{{ URL::to('admin/posts') }}">{{ Lang::get('redminportal::menus.posts') }}</a></li>
    <li class="active"><span class="navbar-text">{{ Lang::get('redminportal::forms.create') }}</span></li>
@stop

@section('content')
    
    @include('redminportal::partials.errors')

    {!! Form::open(array('files' => TRUE, 'action' => '\Redooor\Redminportal\App\Http\Controllers\PostController@postStore', 'role' => 'form')) !!}

    <div class='row'>
        <div class="col-md-3 col-md-push-9">
            <div class="well">
                <div class='form-actions'>
                    {!! HTML::link('admin/posts', Lang::get('redminportal::buttons.cancel'), array('class' => 'btn btn-link btn-sm'))!!}
                    {!! Form::submit(Lang::get('redminportal::buttons.create'), array('class' => 'btn btn-primary btn-sm pull-right')) !!}
                </div>
            </div>
            <div class='well well-small'>
                <div class="form-group">
                    <div class="checkbox">
                        <label for="featured-checker">
                            <input id="featured-checker" checked="checked" name="featured" type="checkbox" value="1"> {{ Lang::get('redminportal::forms.featured') }}
                        </label>
                    </div>
                </div>
                
                <div class="form-group">
                    <div class="checkbox">
                        <label for="private-checker">
                            <input id="private-checker" checked="checked" name="private" type="checkbox" value="1"> {{ Lang::get('redminportal::forms.private') }}
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
                    <h4 class="panel-title">{{ Lang::get('redminportal::forms.create_post') }}</h4>
                </div>
                <div class="panel-body">
                    <ul class="nav nav-tabs" id="lang-selector">
                       @foreach(\Config::get('redminportal::translation') as $translation)
                       <li><a href="#lang-{{ $translation['lang'] }}">{{ $translation['name'] }}</a></li>
                       @endforeach
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane active" id="lang-en">
                            <div class="form-group">
                                {!! Form::label('title', Lang::get('redminportal::forms.title')) !!}
                                {!! Form::text('title', null, array('class' => 'form-control')) !!}
                            </div>

                            <div class="form-group">
                                {!! Form::label('slug', Lang::get('redminportal::forms.slug')) !!}
                                {!! Form::text('slug', null, array('class' => 'form-control')) !!}
                            </div>

                            <div class="form-group">
                                {!! Form::label('content', Lang::get('redminportal::forms.content')) !!}
                                {!! Form::textarea('content', null, array('class' => 'form-control', 'style' => 'height:400px')) !!}
                            </div>
                        </div>
                        @foreach(\Config::get('redminportal::translation') as $translation)
                            @if($translation['lang'] != 'en')
                            <div class="tab-pane" id="lang-{{ $translation['lang'] }}">
                                <div class="form-group">
                                    {!! Form::label($translation['lang'] . '_title', Lang::get('redminportal::forms.title')) !!}
                                    {!! Form::text($translation['lang'] . '_title', null, array('class' => 'form-control')) !!}
                                </div>

                                <div class="form-group">
                                    {!! Form::label($translation['lang'] . '_slug', Lang::get('redminportal::forms.slug')) !!}
                                    {!! Form::text($translation['lang'] . '_slug', null, array('class' => 'form-control')) !!}
                                </div>

                                <div class="form-group">
                                    {!! Form::label($translation['lang'] . '_content', Lang::get('redminportal::forms.content')) !!}
                                    {!! Form::textarea($translation['lang'] . '_content', null, array('class' => 'form-control', 'style' => 'height:400px')) !!}
                                </div>
                            </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    {!! Form::close() !!}
@stop

@section('footer')
    @parent
    <script src="{{ URL::to('vendor/redooor/redminportal/js/bootstrap-fileupload.js') }}"></script>
    <script>
        !function ($) {
            $(function(){
                $('#lang-selector li').first().addClass('active');
                $('#lang-selector a').click(function (e) {
                    e.preventDefault();
                    $(this).tab('show');
                });
            })
        }(window.jQuery);
    </script>
    @include('redminportal::plugins/tinymce')
@stop
