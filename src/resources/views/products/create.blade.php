@extends('redminportal::layouts.master')

@section('head')
<style>
    .form-inline {
        margin-bottom: 15px;
    }
</style>
@stop

@section('navbar-breadcrumb')
    <li><a href="{{ URL::to('admin/products') }}">{{ Lang::get('redminportal::menus.products') }}</a></li>
    <li class="active"><span class="navbar-text">{{ Lang::get('redminportal::forms.create') }}</span></li>
@stop

@section('content')
    
    @include('redminportal::partials.errors')

    {!! Form::open(array('files' => TRUE, 'action' => '\Redooor\Redminportal\App\Http\Controllers\ProductController@postStore', 'role' => 'form')) !!}

    	<div class='row'>
            <div class="col-md-3 col-md-push-9">
                <div class="well">
                    <div class='form-actions'>
                        {!! HTML::link('admin/products', Lang::get('redminportal::buttons.cancel'), array('class' => 'btn btn-link btn-sm'))!!}
                        {!! Form::submit(Lang::get('redminportal::buttons.create'), array('class' => 'btn btn-primary btn-sm pull-right')) !!}
                    </div>
                </div>
                <div class='well well-small'>
                    <div class="form-group">
                        <div class="checkbox">
                            <label for="featured-checker">
                                {!! Form::checkbox('featured', true, true, array('id' => 'featured-checker')) !!} {{ Lang::get('redminportal::forms.featured') }}
                            </label>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="checkbox">
                            <label for="active-checker">
                                {!! Form::checkbox('active', true, true, array('id' => 'active-checker')) !!} {{ Lang::get('redminportal::forms.active') }}
                            </label>
                        </div>
                    </div>
                </div>
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <div class="panel-title">{{ Lang::get('redminportal::forms.category') }}</div>
                    </div>
                    <div class="panel-body">
                        {!! Form::hidden('category_id', null, array('id' => 'category_id')) !!}
                        <ul class="redooor-hierarchy">
                        @foreach ($categories as $item)
                            <li>{!! $item->printCategory() !!}</li>
                        @endforeach
                        </ul>
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
                        <h4 class="panel-title">{{ Lang::get('redminportal::forms.create_product') }}</h4>
                    </div>
                    <div class="panel-body">
                        @include('redminportal::partials.lang-selector-form')
                    </div>
                </div>
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h4 class="panel-title">{{ Lang::get('redminportal::forms.product_properties') }}</h4>
                    </div>
                    <div class="panel-body">
                        <div class="form-group">
                            {!! Form::label('sku', Lang::get('redminportal::forms.sku')) !!}
                            {!! Form::text('sku', null, array('class' => 'form-control')) !!}
                        </div>
                        <div class="form-group">
                            {!! Form::label('price', Lang::get('redminportal::forms.price')) !!}
                            <div class="input-group">
                                <span class="input-group-addon">$</span>
                                {!! Form::text('price', null, array('class' => 'form-control')) !!}
                            </div>
                        </div>
                        <div class="form-group">
                            {!! Form::label('tags', Lang::get('redminportal::forms.tags_separated_by_comma')) !!}
                            {!! Form::text('tags', null, array('class' => 'form-control')) !!}
                        </div>
                    </div>
                </div>
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h4 class="panel-title">
                            {{ Lang::get('redminportal::forms.product_shipping_properties') }}
                        </h4>
                    </div>
                    <div class="panel-body">
                        <!-- Weight information -->
                        <div class="form-group">
                            <label>{{ Lang::get('redminportal::forms.weight') }}</label>
                            <div class="form-inline">
                                @include('redminportal::partials.form-input', [
                                    'label' => Lang::get('redminportal::forms.weight'),
                                    'label_classes' => 'sr-only',
                                    'input_name' => 'weight',
                                    'input_options' => ['type' => 'number', 'step' => '0.001', 'placeholder' => '0.00']
                                ])
                                @include('redminportal::partials.form-select-option', [
                                    'label' => Lang::get('redminportal::forms.weight_unit'),
                                    'label_classes' => 'sr-only',
                                    'select_name' => 'weight_unit',
                                    'select_options' => $weight_units,
                                    'value_as_key' => true
                                ])
                            </div>
                        </div>
                        <!-- Volume information -->
                        <div class="form-group">
                            <label>{{ Lang::get('redminportal::forms.volume') }}</label>
                            <div class="form-inline">
                                @include('redminportal::partials.form-input', [
                                    'label' => Lang::get('redminportal::forms.length'),
                                    'label_classes' => 'sr-only',
                                    'input_name' => 'length',
                                    'input_options' => ['type' => 'number', 'step' => '0.001', 'placeholder' => '0.00'],
                                    'help_text' => Lang::get('redminportal::forms.length')
                                ])
                                @include('redminportal::partials.form-input', [
                                    'label' => Lang::get('redminportal::forms.width'),
                                    'label_classes' => 'sr-only',
                                    'input_name' => 'width',
                                    'input_options' => ['type' => 'number', 'step' => '0.001', 'placeholder' => '0.00'],
                                    'help_text' => Lang::get('redminportal::forms.width')
                                ])
                                @include('redminportal::partials.form-input', [
                                    'label' => Lang::get('redminportal::forms.height'),
                                    'label_classes' => 'sr-only',
                                    'input_name' => 'height',
                                    'input_options' => ['type' => 'number', 'step' => '0.001', 'placeholder' => '0.00'],
                                    'help_text' => Lang::get('redminportal::forms.height')
                                ])
                                @include('redminportal::partials.form-select-option', [
                                    'label' => Lang::get('redminportal::forms.volume_unit'),
                                    'label_classes' => 'sr-only',
                                    'select_name' => 'volume_unit',
                                    'select_options' => $volume_units,
                                    'value_as_key' => true,
                                    'help_text' => Lang::get('redminportal::messages.unit_applies_to_all_dimensions')
                                ])
                            </div>
                        </div>
                    </div>
                </div>
	        </div>
        </div>
    {!! Form::close() !!}
@stop

@section('footer')
    <script src="{{ URL::to('vendor/redooor/redminportal/js/bootstrap-fileupload.js') }}"></script>
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
