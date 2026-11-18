@extends(request()->ajax() ? 'redminportal::layouts.fragment' : 'redminportal::layouts.master')

@section('content')
    
    @include('redminportal::partials.errors')
    
    @if (isset($product))
    <!-- if product exists (START) -->
    @php
        $editorImages = array();
        foreach ($product->images as $image) {
            $editorImages[] = array(
                'title' => pathinfo($image->path)['filename'],
                'value' => url($imagine->getUrl($image->path, 'large')),
            );
        }
    @endphp
    <div data-editor-images="{{ json_encode($editorImages) }}">
    <form method="POST" action="{{ action('\Redooor\Redminportal\App\Http\Controllers\ProductController@postStore') }}" enctype="multipart/form-data" role="form" data-fragment-form>
    @csrf
    <input type="hidden" name="id" id="variant-id" value="{{ old('id', $product->id) }}">
    <input type="hidden" name="product_id" id="variant-product_id" value="{{ old('product_id', $product_id) }}">

    	<div class='row'>
	        <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0" data-fragment-title>{{ Lang::get('redminportal::forms.create_product_variation') }}</h4>
                    </div>
                    <div class="card-body">
                        @include('redminportal::partials.lang-selector-form', [
                            'selector_name' => '-variant',
                            'id_prefix' => 'variant-',
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
                            <label for="variant-sku">{{ Lang::get('redminportal::forms.sku') }}</label>
                            <input type="text" name="sku" id="variant-sku" value="{{ old('sku', old('sku', $product->sku)) }}" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label for="variant-price">{{ Lang::get('redminportal::forms.price') }}</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="text" name="price" id="variant-price" value="{{ old('price', old('price', $product->price)) }}" class="form-control">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="variant-tags">{{ Lang::get('redminportal::forms.tags_separated_by_comma') }}</label>
                            <input type="text" name="tags" id="variant-tags" value="{{ old('tags', old('tags', $tagString)) }}" class="form-control" data-redmin-tags="">
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
                            <span class="form-label d-block">{{ Lang::get('redminportal::forms.weight') }}</span>
                            <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                                {!! Redminportal::form()->inputer('weight', old('weight', $product->weight), [
                                    'label' => Lang::get('redminportal::forms.weight'),
                                    'label_classes' => 'visually-hidden',
                                    'id' => 'variant-weight',
                                    'type' => 'number',
                                    'step' => '0.001',
                                    'placeholder' => '0.00'
                                ]) !!}
                                {!! Redminportal::form()->selector('weight_unit', $weight_units, old('weight_unit', $product->weight_unit), [
                                    'label' => Lang::get('redminportal::forms.weight_unit'),
                                    'label_classes' => 'visually-hidden',
                                    'id' => 'variant-weight_unit',
                                    'value_as_key' => true
                                ]) !!}
                            </div>
                        </div>
                        <!-- Volume information -->
                        <div class="mb-3">
                            <span class="form-label d-block">{{ Lang::get('redminportal::forms.volume') }}</span>
                            <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                                {!! Redminportal::form()->inputer('length', old('length', $product->length), [
                                    'label' => Lang::get('redminportal::forms.length'),
                                    'label_classes' => 'visually-hidden',
                                    'id' => 'variant-length',
                                    'type' => 'number',
                                    'step' => '0.001',
                                    'placeholder' => '0.00',
                                    'help_text' => Lang::get('redminportal::forms.length')
                                ]) !!}
                                {!! Redminportal::form()->inputer('width', old('width', $product->width), [
                                    'label' => Lang::get('redminportal::forms.width'),
                                    'label_classes' => 'visually-hidden',
                                    'id' => 'variant-width',
                                    'type' => 'number',
                                    'step' => '0.001',
                                    'placeholder' => '0.00',
                                    'help_text' => Lang::get('redminportal::forms.width')
                                ]) !!}
                                {!! Redminportal::form()->inputer('height', old('height', $product->height), [
                                    'label' => Lang::get('redminportal::forms.height'),
                                    'label_classes' => 'visually-hidden',
                                    'id' => 'variant-height',
                                    'type' => 'number',
                                    'step' => '0.001',
                                    'placeholder' => '0.00',
                                    'help_text' => Lang::get('redminportal::forms.height')
                                ]) !!}
                                {!! Redminportal::form()->selector('volume_unit', $volume_units, old('volume_unit', $product->volume_unit), [
                                    'label' => Lang::get('redminportal::forms.volume_unit'),
                                    'label_classes' => 'visually-hidden',
                                    'id' => 'variant-volume_unit',
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
                                <img src="{{ asset($imagine->getUrl($image->path)) }}" alt="{{ $product->name }}" class="img-thumbnail">
                                <br><br>
                                <div class="btn-group btn-group-sm">
                                    @permitted('admin/products/variant-imgremove/' . $product_id . '/' . $image->id)
                                    @if (request()->ajax())
                                    <a href="{{ URL::to('admin/products/variant-imgremove/' . $product_id . '/' . $image->id) }}" class="btn btn-danger" data-fragment-post data-fragment-confirm="{{ Lang::get('redminportal::messages.are_you_sure_you_want_to_delete') }}" data-fragment-confirm-yes="{{ Lang::get('redminportal::buttons.delete_yes') }}" data-fragment-confirm-no="{{ Lang::get('redminportal::buttons.delete_no') }}" aria-label="{{ Lang::get('redminportal::forms.remove_images') }}">
                                        <i class="fa-solid fa-xmark"></i>
                                    </a>
                                    @else
                                    <a href="{{ URL::to('admin/products/variant-imgremove/' . $product_id . '/' . $image->id) }}" class="btn btn-danger btn-confirm">
                                        <i class="fa-solid fa-xmark"></i>
                                    </a>
                                    @endif
                                    @endpermitted
                                    <a href="{{ URL::to($imagine->getUrl($image->path, 'large')) }}" class="btn btn-primary btn-copy">
                                        <i class="fa-solid fa-link"></i>
                                    </a>
                                    @if (request()->ajax())
                                    <a href="{{ URL::to($imagine->getUrl($image->path, 'large')) }}" class="btn btn-info" target="_blank" rel="noopener" aria-label="{{ Lang::get('redminportal::buttons.view_image') }}">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                    @else
                                    <a href="{{ URL::to($imagine->getUrl($image->path, 'large')) }}" class="btn btn-info" target="_blank" data-lightbox="uploaded-images" data-lightbox-title="{{ $image->path }}" aria-label="{{ Lang::get('redminportal::buttons.view_image') }}">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                    @endif
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
                        <input type="submit" value="{{ Lang::get('redminportal::buttons.save') }}" class="btn btn-primary btn-sm">
                    </div>
                </div>
                <div class='card card-body bg-body-tertiary p-2'>
                    <div class="mb-3">
                        <div class="form-check">
                            <label class="form-check-label" for="variant-featured-checker">
                                <input type="checkbox" name="featured" value="1" id="variant-featured-checker" class="form-check-input" @checked(session()->hasOldInput() ? old('featured') : $product->featured)> {{ Lang::get('redminportal::forms.featured') }}
                            </label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <label class="form-check-label" for="variant-active-checker">
                                <input type="checkbox" name="active" value="1" id="variant-active-checker" class="form-check-input" @checked(session()->hasOldInput() ? old('active') : $product->active)> {{ Lang::get('redminportal::forms.active') }}
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
                               id="variant-category_id" 
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
                    @include('redminportal::partials.image-input')
                </div>
                <div class="card card-body bg-body-tertiary d-md-none">
                    <div class='form-actions text-end'>
                        <input type="submit" value="{{ Lang::get('redminportal::buttons.create') }}" class="btn btn-primary btn-sm">
                    </div>
                </div>
	        </div>
        </div>
    </form>
    </div>
    @if (! request()->ajax())
    @include('redminportal::partials.confirm-delete')
    @endif
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
