@extends('redminportal::layouts.master')

@section('navbar-breadcrumb')
    <li class="nav-item"><a class="nav-link" href="{{ URL::to('admin/products') }}">{{ Lang::get('redminportal::menus.products') }}</a></li>
    <li class="nav-item"><span class="nav-link active">{{ Lang::get('redminportal::forms.create') }}</span></li>
@stop

@section('content')
    
    @include('redminportal::partials.errors')

    {!! Form::open(array('files' => TRUE, 'action' => '\Redooor\Redminportal\App\Http\Controllers\ProductController@postStore', 'role' => 'form')) !!}

    	<div class='row'>
            <div class="col-lg-3 order-lg-last">
                <div class="card card-body bg-body-tertiary">
                    <div class='form-actions'>
                        {!! HTML::link('admin/products', Lang::get('redminportal::buttons.cancel'), array('class' => 'btn btn-link btn-sm'))!!}
                        {!! Form::submit(Lang::get('redminportal::buttons.create'), array('class' => 'btn btn-primary btn-sm float-end')) !!}
                    </div>
                </div>
                <div class='card card-body bg-body-tertiary p-2'>
                    <div class="mb-3">
                        <div class="form-check">
                            <label class="form-check-label" for="featured-checker">
                                <input class="form-check-input" id="featured-checker" checked="checked" name="featured" type="checkbox" value="1"> {{ Lang::get('redminportal::forms.featured') }}
                            </label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <label class="form-check-label" for="active-checker">
                                <input class="form-check-input" id="active-checker" checked="checked" name="active" type="checkbox" value="1"> {{ Lang::get('redminportal::forms.active') }}
                            </label>
                        </div>
                    </div>
                </div>
                {{-- Load Select Category partial form --}}
                @include('redminportal::partials.form-select-category', [
                    'select_category_selected_name' => 'category_id',
                    'select_category_categories' => $categories,
                    'select_category_required_field' => true
                ])
		        <div>
                    <div class="fileupload fileupload-new" data-provides="fileupload">
                      <div class="fileupload-preview thumbnail" style="width: 200px; height: 150px;"></div>
                      <div>
                        <span class="btn btn-secondary btn-file"><span class="fileupload-new">{{ Lang::get('redminportal::forms.select_image') }}</span><span class="fileupload-exists">{{ Lang::get('redminportal::forms.change_image') }}</span><input name="image" type="file"></span>
                        <a href="#" class="btn btn-danger fileupload-exists" data-dismiss="fileupload">{{ Lang::get('redminportal::forms.remove_image') }}</a>
                      </div>
                    </div>
                </div>
	        </div>

	        <div class="col-lg-9 order-lg-first">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0">{{ Lang::get('redminportal::forms.create_product') }}</h4>
                    </div>
                    <div class="card-body">
                        @include('redminportal::partials.lang-selector-form')
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0">{{ Lang::get('redminportal::forms.product_properties') }}</h4>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            {!! Form::label('sku', Lang::get('redminportal::forms.sku')) !!}
                            {!! Form::text('sku', null, array('class' => 'form-control')) !!}
                        </div>
                        <div class="mb-3">
                            {!! Form::label('price', Lang::get('redminportal::forms.price')) !!}
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                {!! Form::text('price', null, array('class' => 'form-control')) !!}
                            </div>
                        </div>
                        <div class="mb-3">
                            {!! Form::label('tags', Lang::get('redminportal::forms.tags_separated_by_comma')) !!}
                            {!! Form::text('tags', null, array('class' => 'form-control')) !!}
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0">
                            {{ Lang::get('redminportal::forms.product_shipping_properties') }}
                        </h4>
                    </div>
                    <div class="card-body">
                        <!-- Weight information -->
                        <div class="mb-3">
                            <label>{{ Lang::get('redminportal::forms.weight') }}</label>
                            <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                                @include('redminportal::partials.form-input', [
                                    'label' => Lang::get('redminportal::forms.weight'),
                                    'label_classes' => 'visually-hidden',
                                    'input_name' => 'weight',
                                    'input_options' => ['type' => 'number', 'step' => '0.001', 'placeholder' => '0.00']
                                ])
                                @include('redminportal::partials.form-select-option', [
                                    'label' => Lang::get('redminportal::forms.weight_unit'),
                                    'label_classes' => 'visually-hidden',
                                    'select_name' => 'weight_unit',
                                    'select_options' => $weight_units,
                                    'value_as_key' => true
                                ])
                            </div>
                        </div>
                        <!-- Volume information -->
                        <div class="mb-3">
                            <label>{{ Lang::get('redminportal::forms.volume') }}</label>
                            <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                                @include('redminportal::partials.form-input', [
                                    'label' => Lang::get('redminportal::forms.length'),
                                    'label_classes' => 'visually-hidden',
                                    'input_name' => 'length',
                                    'input_options' => ['type' => 'number', 'step' => '0.001', 'placeholder' => '0.00'],
                                    'help_text' => Lang::get('redminportal::forms.length')
                                ])
                                @include('redminportal::partials.form-input', [
                                    'label' => Lang::get('redminportal::forms.width'),
                                    'label_classes' => 'visually-hidden',
                                    'input_name' => 'width',
                                    'input_options' => ['type' => 'number', 'step' => '0.001', 'placeholder' => '0.00'],
                                    'help_text' => Lang::get('redminportal::forms.width')
                                ])
                                @include('redminportal::partials.form-input', [
                                    'label' => Lang::get('redminportal::forms.height'),
                                    'label_classes' => 'visually-hidden',
                                    'input_name' => 'height',
                                    'input_options' => ['type' => 'number', 'step' => '0.001', 'placeholder' => '0.00'],
                                    'help_text' => Lang::get('redminportal::forms.height')
                                ])
                                @include('redminportal::partials.form-select-option', [
                                    'label' => Lang::get('redminportal::forms.volume_unit'),
                                    'label_classes' => 'visually-hidden',
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
    @parent
    <script src="{{ URL::to('vendor/redooor/redminportal/js/bootstrap-fileupload.js') }}"></script>
    <script>
        !function ($) {
            $(function(){
            })
        }(window.jQuery);
    </script>
    @include('redminportal::plugins/tinymce')
    @include('redminportal::plugins/tagsinput')
@stop
