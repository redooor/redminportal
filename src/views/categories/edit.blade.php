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

    {{ Form::open(array('files' => TRUE, 'action' => 'Redooor\Redminportal\CategoryController@postStore', 'role' => 'form')) }}
        {{ Form::hidden('id', $category->id)}}

        <div class='row'>
            <div class="col-md-3 col-md-push-9">
                <div class='form-actions text-right'>
                    {{ HTML::link('admin/categories', 'Cancel', array('class' => 'btn btn-default'))}}
                    {{ Form::submit('Save Changes', array('class' => 'btn btn-primary')) }}
                </div>
                <hr>
                <div class='well well-small'>
                    <div class="form-group">
                        <label for="active" class="checkbox inline">
                            {{ Form::checkbox('active', $category->active, $category->active, array('id' => 'active-checker')) }} Active
                        </label>
                    </div>
                </div>
                <div class="form-group">
                    {{ Form::label('parent_id', 'Parent Category') }}
                    {{ Form::select('parent_id', $categories, $category->category_id, array('class' => 'form-control')) }}
                </div>
                <div class="form-group">
                    {{ Form::label('order', 'Priority Order') }}
                    {{ Form::input('number', 'order', $category->order, array('class' => 'form-control', 'min' => '0', 'required')) }}
                </div>
                <div>
                    <div class="fileupload fileupload-new" data-provides="fileupload">
                      <div class="fileupload-preview thumbnail" style="width: 200px; height: 150px;"></div>
                      <div>
                        <span class="btn btn-default btn-file"><span class="fileupload-new">Select image</span><span class="fileupload-exists">Change</span>{{ Form::file('image') }}</span>
                        <a href="#" class="btn btn-danger fileupload-exists" data-dismiss="fileupload">Remove</a>
                      </div>
                    </div>
                </div>
            </div>

            <div class="col-md-9 col-md-pull-3">
                <ul class="nav nav-tabs" id="lang-selector">
                    @foreach(\Config::get('redminportal::translation') as $translation)
                    <li><a href="#lang-{{ $translation['lang'] }}">{{ $translation['name'] }}</a></li>
                    @endforeach
                </ul>
                <div class="tab-content">
                    <div class="tab-pane active" id="lang-en">
                        <div class="form-group">
                            {{ Form::label('name', 'Title') }}
                            {{ Form::text('name', $category->name, array('class' => 'form-control', 'required')) }}
                        </div>

                        <div class="form-group">
                            {{ Form::label('short_description', 'Summary') }}
                            {{ Form::text('short_description', $category->short_description, array('class' => 'form-control', 'required')) }}
                        </div>

                        <div class="form-group">
                            {{ Form::label('long_description', 'Description') }}
                            {{ Form::textarea('long_description', $category->long_description, array('class' => 'form-control')) }}
                        </div>
                    </div>
                    @foreach(\Config::get('redminportal::translation') as $translation)
                        @if($translation['lang'] != 'en')
                        <div class="tab-pane" id="lang-{{ $translation['lang'] }}">
                            <div class="form-group">
                                {{ Form::label($translation['lang'] . '_name', 'Title') }}
                                {{ Form::text($translation['lang'] . '_name', $category_cn->name, array('class' => 'form-control')) }}
                            </div>

                            <div class="form-group">
                                {{ Form::label($translation['lang'] . '_short_description', 'Summary') }}
                                {{ Form::text($translation['lang'] . '_short_description', $category_cn->short_description, array('class' => 'form-control')) }}
                            </div>

                            <div class="form-group">
                                {{ Form::label($translation['lang'] . '_long_description', 'Description') }}
                                {{ Form::textarea($translation['lang'] . '_long_description', $category_cn->long_description, array('class' => 'form-control')) }}
                            </div>
                        </div>
                        @endif
                    @endforeach
                </div>
                <h4>Uploaded Photos</h4>
                <div class='row'>
                    @foreach( $category->images as $image )
                    <div class='col-md-3'>
                        {{ HTML::image($imageUrl . $image->path, $category->name, array('class' => 'img-thumbnail', 'alt' => $image->path)) }}
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    {{ Form::close() }}
@stop

@section('footer')
    <script src="{{ URL::to('packages/redooor/redminportal/assets/js/bootstrap-fileupload.js') }}"></script>
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
