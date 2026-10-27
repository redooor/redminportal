@extends('redminportal::layouts.bare')

@section('content')
    
    @include('redminportal::partials.errors')

    {!! Form::open(array('files' => TRUE, 'action' => '\Redooor\Redminportal\App\Http\Controllers\ProductController@postStore', 'role' => 'form')) !!}

    {!! Form::hidden('product_id', $product_id, array('id' => 'product_id')) !!}

    	<div class='row'>
	        <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0">{{ Lang::get('redminportal::forms.create_product_variation') }}</h4>
                    </div>
                    <div class="card-body">
                        @include('redminportal::partials.lang-selector-form', ['selector_name' => '-variant'])
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0">{{ Lang::get('redminportal::forms.product_properties') }}</h4>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            {!! Form::label('sku', Lang::get('redminportal::forms.sku')) !!}
                            {!! Form::text('sku', old('sku'), array('class' => 'form-control')) !!}
                        </div>
                        <div class="mb-3">
                            {!! Form::label('price', Lang::get('redminportal::forms.price')) !!}
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                {!! Form::text('price', old('price'), array('class' => 'form-control')) !!}
                            </div>
                        </div>
                        <div class="mb-3">
                            {!! Form::label('tags', Lang::get('redminportal::forms.tags_separated_by_comma')) !!}
                            {!! Form::text('tags', old('tags'), array('class' => 'form-control')) !!}
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
                                {!! Redminportal::form()->inputer('weight', old('weight'), [
                                    'label' => Lang::get('redminportal::forms.weight'),
                                    'label_classes' => 'visually-hidden',
                                    'type' => 'number',
                                    'step' => '0.001',
                                    'placeholder' => '0.00'
                                ]) !!}
                                {!! Redminportal::form()->selector('weight_unit', $weight_units, old('weight_unit'), [
                                    'label' => Lang::get('redminportal::forms.weight_unit'),
                                    'label_classes' => 'visually-hidden',
                                    'value_as_key' => true
                                ]) !!}
                            </div>
                        </div>
                        <!-- Volume information -->
                        <div class="mb-3">
                            <label>{{ Lang::get('redminportal::forms.volume') }}</label>
                            <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                                {!! Redminportal::form()->inputer('length', old('length'), [
                                    'label' => Lang::get('redminportal::forms.length'),
                                    'label_classes' => 'visually-hidden',
                                    'type' => 'number',
                                    'step' => '0.001',
                                    'placeholder' => '0.00',
                                    'help_text' => Lang::get('redminportal::forms.length')
                                ]) !!}
                                {!! Redminportal::form()->inputer('width', old('width'), [
                                    'label' => Lang::get('redminportal::forms.width'),
                                    'label_classes' => 'visually-hidden',
                                    'type' => 'number',
                                    'step' => '0.001',
                                    'placeholder' => '0.00',
                                    'help_text' => Lang::get('redminportal::forms.width')
                                ]) !!}
                                {!! Redminportal::form()->inputer('height', old('height'), [
                                    'label' => Lang::get('redminportal::forms.height'),
                                    'label_classes' => 'visually-hidden',
                                    'type' => 'number',
                                    'step' => '0.001',
                                    'placeholder' => '0.00',
                                    'help_text' => Lang::get('redminportal::forms.height')
                                ]) !!}
                                {!! Redminportal::form()->selector('volume_unit', $volume_units, old('volume_unit'), [
                                    'label' => Lang::get('redminportal::forms.volume_unit'),
                                    'label_classes' => 'visually-hidden',
                                    'value_as_key' => true,
                                    'help_text' => Lang::get('redminportal::messages.unit_applies_to_all_dimensions')
                                ]) !!}
                            </div>
                        </div>
                    </div>
                </div>
	        </div>
            <div class="col-md-4">
                <div class="card card-body bg-body-tertiary d-none d-md-block">
                    <div class='form-actions text-end'>
                        {!! Form::submit(Lang::get('redminportal::buttons.create'), array('class' => 'btn btn-primary btn-sm')) !!}
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
                <div class="card">
                    <div class="card-header">
                        <div class="card-title mb-0">{{ trans('redminportal::forms.category') }}</div>
                    </div>
                    <div class="card-body">
                        <input type="hidden" 
                               id="category_id" 
                               name="category_id" 
                               value="{{ $product->category_id }}">
                        <ul class="redmin-hierarchy">
                            <li><a class="active">{{ $product->category->name }}</a></li>
                        </ul>
                    </div>
                    <div class="card-footer">
                        <i><small>{{ trans('redminportal::messages.product_variant_inherits_from_parent_footnote') }}</small></i>
                    </div>
                </div>
		        <div>
                    <div class="fileupload fileupload-new" data-provides="fileupload">
                      <div class="fileupload-preview thumbnail" style="width: 200px; height: 150px;"></div>
                      <div>
                        <span class="btn btn-secondary btn-file"><span class="fileupload-new">{{ Lang::get('redminportal::forms.select_image') }}</span><span class="fileupload-exists">{{ Lang::get('redminportal::forms.change_image') }}</span><input name="image" type="file"></span>
                        <a href="#" class="btn btn-danger fileupload-exists" data-dismiss="fileupload">{{ Lang::get('redminportal::forms.remove_image') }}</a>
                      </div>
                    </div>
                </div>
                <div class="card card-body bg-body-tertiary d-md-none">
                    <div class='form-actions text-end'>
                        {!! Form::submit(Lang::get('redminportal::buttons.create'), array('class' => 'btn btn-primary btn-sm')) !!}
                    </div>
                </div>
	        </div>
        </div>
    {!! Form::close() !!}
@stop

@section('footer')
    @parent
    <script src="{{ URL::to('vendor/redooor/redminportal/js/bootstrap-fileupload.js') }}"></script>
    @include('redminportal::plugins/tinymce')
    @include('redminportal::plugins/tagsinput')
@stop
