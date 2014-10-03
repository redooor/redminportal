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

    {{ Form::open(array('files' => TRUE, 'action' => 'Redooor\Redminportal\MediaController@postStore', 'role' => 'form')) }}

    	<div class='row'>
            <div class="col-md-3 col-md-push-9">
	    		<div class='form-actions text-right'>
                    {{ HTML::link('admin/medias', 'Cancel', array('class' => 'btn btn-default'))}}
                    {{ Form::submit('Create', array('class' => 'btn btn-primary')) }}
                </div>
                <hr>
                <div class='well well-small'>
                    <div class="form-group">
                        <label for="active" class="checkbox inline">
                            {{ Form::checkbox('featured', true, true, array('id' => 'featured-checker')) }} Featured
                        </label>
                    </div>

                    <div class="form-group">
                        <label for="active" class="checkbox inline">
                            {{ Form::checkbox('active', true, true, array('id' => 'active-checker')) }} Active
                        </label>
                    </div>
                </div>
                <div class="form-group">
                    {{ Form::label('category_id', 'Category') }}
                    {{ Form::hidden('category_id', Input::old('category_id'))}}
                    <ul class="redooor-hierarchy">
                    @foreach ($categories as $category)
                        <li>{{ Redooor\Redminportal\Category::printCategory($category->id) }}</li>
                    @endforeach
                    </ul>
                </div>
		        <div class="form-group">
			        {{ Form::label('sku', 'SKU') }}
			        {{ Form::text('sku', Input::old('sku'), array('class' => 'form-control')) }}
		        </div>
		        <div class="form-group">
			        {{ Form::label('tags', 'Tags (separated by comma)') }}
			        {{ Form::text('tags', Input::old('tags'), array('class' => 'form-control')) }}
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
                            {{ Form::text('name', Input::old('name'), array('class' => 'form-control')) }}
                        </div>

                        <div class="form-group">
                            {{ Form::label('short_description', 'Summary') }}
                            {{ Form::text('short_description', Input::old('short_description'), array('class' => 'form-control')) }}
                        </div>

                        <div class="form-group">
                            {{ Form::label('long_description', 'Description') }}
                            {{ Form::textarea('long_description', Input::old('long_description'), array('class' => 'form-control')) }}
                        </div>
                    </div>
                    @foreach(\Config::get('redminportal::translation') as $translation)
                        @if($translation['lang'] != 'en')
                        <div class="tab-pane" id="lang-{{ $translation['lang'] }}">
                            <div class="form-group">
                                {{ Form::label($translation['lang'] . '_name', 'Title') }}
                                {{ Form::text($translation['lang'] . '_name', Input::old($translation['lang'] . '_name'), array('class' => 'form-control')) }}
                            </div>

                            <div class="form-group">
                                {{ Form::label($translation['lang'] . '_short_description', 'Summary') }}
                                {{ Form::text($translation['lang'] . '_short_description', Input::old($translation['lang'] . '_short_description'), array('class' => 'form-control')) }}
                            </div>

                            <div class="form-group">
                                {{ Form::label($translation['lang'] . '_long_description', 'Description') }}
                                {{ Form::textarea($translation['lang'] . '_long_description', Input::old($translation['lang'] . '_long_description'), array('class' => 'form-control')) }}
                            </div>
                        </div>
                        @endif
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
                // On load, check if previous category exists for error message
                function checkCategory() {
                    $selected_val = $('#category_id').val();
                    if ($selected_val != '') {
                        $('.redooor-hierarchy a').each(function() {
                            if ($(this).attr('href') == $selected_val) {
                                $(this).addClass('active');
                            }
                        });
                    }
                }
                checkCategory();
                // Change selected category
                $(document).on('click', '.redooor-hierarchy a', function(e) {
                    e.preventDefault();
                    $selected = $(this).attr('href');
                    $('#category_id').val($selected);
                    $('.redooor-hierarchy a.active').removeClass('active');
                    $(this).addClass('active');
                });
            })
        }(window.jQuery);
    </script>
    @include('redminportal::plugins/tinymce')
@stop
