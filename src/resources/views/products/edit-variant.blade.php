@extends('redminportal::layouts.bare')

@section('content')
    
    @include('redminportal::partials.errors')
    
    @if (isset($product))
    <!-- if product exists (START) -->
    {!! Form::open(array('files' => TRUE, 'action' => '\Redooor\Redminportal\App\Http\Controllers\ProductController@postStore', 'role' => 'form')) !!}
    {!! Form::hidden('id', $product->id) !!}
    {!! Form::hidden('product_id', $product_id, array('id' => 'product_id')) !!}

    	<div class='row'>
	        <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0">{{ Lang::get('redminportal::forms.create_product_variation') }}</h4>
                    </div>
                    <div class="card-body">
                        @include('redminportal::partials.lang-selector-form', [
                            'selector_name' => '-variant',
                            'translatable' => $product,
                            'translated' => $translated
                        ])
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0">{{ Lang::get('redminportal::forms.product_properties') }}</h4>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            {!! Form::label('sku', Lang::get('redminportal::forms.sku')) !!}
                            {!! Form::text('sku', $product->sku, array('class' => 'form-control')) !!}
                        </div>
                        <div class="mb-3">
                            {!! Form::label('price', Lang::get('redminportal::forms.price')) !!}
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                {!! Form::text('price', $product->price, array('class' => 'form-control')) !!}
                            </div>
                        </div>
                        <div class="mb-3">
                            {!! Form::label('tags', Lang::get('redminportal::forms.tags_separated_by_comma')) !!}
                            {!! Form::text('tags', $tagString, array('class' => 'form-control')) !!}
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
                                {!! Redminportal::form()->inputer('weight', (old('weight') ? old('weight') : $product->weight), [
                                    'label' => Lang::get('redminportal::forms.weight'),
                                    'label_classes' => 'visually-hidden',
                                    'type' => 'number',
                                    'step' => '0.001',
                                    'placeholder' => '0.00'
                                ]) !!}
                                {!! Redminportal::form()->selector('weight_unit', $weight_units, (old('weight_unit') ? old('weight_unit') : $product->weight_unit), [
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
                                {!! Redminportal::form()->inputer('length', (old('length') ? old('length') : $product->length), [
                                    'label' => Lang::get('redminportal::forms.length'),
                                    'label_classes' => 'visually-hidden',
                                    'type' => 'number',
                                    'step' => '0.001',
                                    'placeholder' => '0.00',
                                    'help_text' => Lang::get('redminportal::forms.length')
                                ]) !!}
                                {!! Redminportal::form()->inputer('width', (old('width') ? old('width') : $product->width), [
                                    'label' => Lang::get('redminportal::forms.width'),
                                    'label_classes' => 'visually-hidden',
                                    'type' => 'number',
                                    'step' => '0.001',
                                    'placeholder' => '0.00',
                                    'help_text' => Lang::get('redminportal::forms.width')
                                ]) !!}
                                {!! Redminportal::form()->inputer('height', (old('height') ? old('height') : $product->height), [
                                    'label' => Lang::get('redminportal::forms.height'),
                                    'label_classes' => 'visually-hidden',
                                    'type' => 'number',
                                    'step' => '0.001',
                                    'placeholder' => '0.00',
                                    'help_text' => Lang::get('redminportal::forms.height')
                                ]) !!}
                                {!! Redminportal::form()->selector('volume_unit', $volume_units, (old('volume_unit') ? old('volume_unit') : $product->volume_unit), [
                                    'label' => Lang::get('redminportal::forms.volume_unit'),
                                    'label_classes' => 'visually-hidden',
                                    'value_as_key' => true,
                                    'help_text' => Lang::get('redminportal::messages.unit_applies_to_all_dimensions')
                                ]) !!}
                            </div>
                        </div>
                    </div>
                </div>
                @if (count($product->images) > 0)
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0">{{ Lang::get('redminportal::forms.uploaded_photos') }}</h4>
                    </div>
                    <div class="card-body">
                        <div class='row'>
                            @foreach ($product->images as $image)
                            <div class='col-md-4'>
                                {!! HTML::image($imagine->getUrl($image->path), $product->name, array('class' => 'img-thumbnail', 'alt' => $image->path)) !!}
                                <br><br>
                                <div class="btn-group btn-group-sm">
                                    @permitted('admin/products/variant-imgremove/' . $product->id . '/' . $image->id)
                                    <a href="{{ URL::to('admin/products/variant-imgremove/' . $product->id . '/' . $image->id) }}" class="btn btn-danger btn-confirm">
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
            <div class="col-md-4">
                <div class="card card-body bg-body-tertiary d-none d-md-block">
                    <div class='form-actions text-end'>
                        {!! Form::submit(Lang::get('redminportal::buttons.save'), array('class' => 'btn btn-primary btn-sm')) !!}
                    </div>
                </div>
                <div class='card card-body bg-body-tertiary p-2'>
                    <div class="mb-3">
                        <div class="form-check">
                            <label class="form-check-label" for="featured-checker">
                                {!! Form::checkbox('featured', $product->featured, $product->featured, array('class' => 'form-check-input', 'id' => 'featured-checker')) !!} {{ Lang::get('redminportal::forms.featured') }}
                            </label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <label class="form-check-label" for="active-checker">
                                {!! Form::checkbox('active', $product->active, $product->active, array('class' => 'form-check-input', 'id' => 'active-checker')) !!} {{ Lang::get('redminportal::forms.active') }}
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
                               value="{{ $parent->category_id }}">
                        <ul class="redmin-hierarchy">
                            <li><a class="active">{{ $parent->category->name }}</a></li>
                        </ul>
                    </div>
                    <div class="card-footer">
                        <i><small>{{ trans('redminportal::messages.product_variant_inherits_from_parent_footnote') }}</small></i>
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
                <div class="card card-body bg-body-tertiary d-md-none">
                    <div class='form-actions text-end'>
                        {!! Form::submit(Lang::get('redminportal::buttons.create'), array('class' => 'btn btn-primary btn-sm')) !!}
                    </div>
                </div>
	        </div>
        </div>
    {!! Form::close() !!}
    @include('redminportal::partials.confirm-delete')
    <!-- if product exists (END) -->
    @endif
@stop

@section('footer')
    @parent
    <script src="{{ URL::to('vendor/redooor/redminportal/js/redmin-fileinput.min.js') }}"></script>
    @if (isset($product))
    @include('redminportal::plugins/editor', ['editorImages' => $product->images])
    @endif
    @include('redminportal::plugins/tagsinput')
@stop
