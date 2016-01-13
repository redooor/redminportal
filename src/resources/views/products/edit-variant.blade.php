@extends('redminportal::layouts.bare')

@section('content')
    
    @include('redminportal::partials.errors')
    
    @if (isset($product))
    <!-- if product exists (START) -->
    {!! Form::open(array('files' => TRUE, 'action' => '\Redooor\Redminportal\App\Http\Controllers\ProductController@postStore', 'role' => 'form')) !!}
    {!! Form::hidden('id', $product->id) !!}
    {!! Form::hidden('product_id', $product_id, array('id' => 'product_id')) !!}

    	<div class='row'>
	        <div class="col-sm-8">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h4 class="panel-title">{{ Lang::get('redminportal::forms.create_product_variation') }}</h4>
                    </div>
                    <div class="panel-body">
                        @include('redminportal::partials.lang-selector-form', [
                            'selector_name' => '-variant',
                            'translatable' => $product,
                            'translated' => $translated
                        ])
                    </div>
                </div>
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h4 class="panel-title">{{ Lang::get('redminportal::forms.product_properties') }}</h4>
                    </div>
                    <div class="panel-body">
                        <div class="form-group">
                            {!! Form::label('sku', Lang::get('redminportal::forms.sku')) !!}
                            {!! Form::text('sku', $product->sku, array('class' => 'form-control')) !!}
                        </div>
                        <div class="form-group">
                            {!! Form::label('price', Lang::get('redminportal::forms.price')) !!}
                            <div class="input-group">
                                <span class="input-group-addon">$</span>
                                {!! Form::text('price', $product->price, array('class' => 'form-control')) !!}
                            </div>
                        </div>
                        <div class="form-group">
                            {!! Form::label('tags', Lang::get('redminportal::forms.tags_separated_by_comma')) !!}
                            {!! Form::text('tags', $tagString, array('class' => 'form-control')) !!}
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
                                    'input_options' => ['type' => 'number', 'step' => '0.001', 'placeholder' => '0.00'],
                                    'input_value' => $product->weight
                                ])
                                @include('redminportal::partials.form-select-option', [
                                    'label' => Lang::get('redminportal::forms.weight_unit'),
                                    'label_classes' => 'sr-only',
                                    'select_name' => 'weight_unit',
                                    'select_options' => $weight_units,
                                    'value_as_key' => true,
                                    'selected' => $product->weight_unit
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
                                    'help_text' => Lang::get('redminportal::forms.length'),
                                    'input_value' => $product->length
                                ])
                                @include('redminportal::partials.form-input', [
                                    'label' => Lang::get('redminportal::forms.width'),
                                    'label_classes' => 'sr-only',
                                    'input_name' => 'width',
                                    'input_options' => ['type' => 'number', 'step' => '0.001', 'placeholder' => '0.00'],
                                    'help_text' => Lang::get('redminportal::forms.width'),
                                    'input_value' => $product->width
                                ])
                                @include('redminportal::partials.form-input', [
                                    'label' => Lang::get('redminportal::forms.height'),
                                    'label_classes' => 'sr-only',
                                    'input_name' => 'height',
                                    'input_options' => ['type' => 'number', 'step' => '0.001', 'placeholder' => '0.00'],
                                    'help_text' => Lang::get('redminportal::forms.height'),
                                    'input_value' => $product->height
                                ])
                                @include('redminportal::partials.form-select-option', [
                                    'label' => Lang::get('redminportal::forms.volume_unit'),
                                    'label_classes' => 'sr-only',
                                    'select_name' => 'volume_unit',
                                    'select_options' => $volume_units,
                                    'value_as_key' => true,
                                    'selected' => $product->volume_unit,
                                    'help_text' => Lang::get('redminportal::messages.unit_applies_to_all_dimensions')
                                ])
                            </div>
                        </div>
                    </div>
                </div>
                @if (count($product->images) > 0)
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h4 class="panel-title">{{ Lang::get('redminportal::forms.uploaded_photos') }}</h4>
                    </div>
                    <div class="panel-body">
                        <div class='row'>
                            @foreach ($product->images as $image)
                            <div class='col-sm-4'>
                                {!! HTML::image($imagine->getUrl($image->path), $product->name, array('class' => 'img-thumbnail', 'alt' => $image->path)) !!}
                                <br><br>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ URL::to('admin/products/variant-imgremove/' . $product->id . '/' . $image->id) }}" class="btn btn-danger btn-confirm">
                                        <span class="glyphicon glyphicon-remove"></span>
                                    </a>
                                    <a href="{{ URL::to($imagine->getUrl($image->path, 'large')) }}" class="btn btn-primary btn-copy">
                                        <span class="glyphicon glyphicon-link"></span>
                                    </a>
                                    <a href="{{ URL::to($imagine->getUrl($image->path, 'large')) }}" class="btn btn-info" target="_blank">
                                        <span class="glyphicon glyphicon-eye-open"></span>
                                    </a>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif
	        </div>
            <div class="col-sm-4">
                <div class="well hidden-xs">
                    <div class='form-actions text-right'>
                        {!! Form::submit(Lang::get('redminportal::buttons.save'), array('class' => 'btn btn-primary btn-sm')) !!}
                    </div>
                </div>
                <div class='well well-small'>
                    <div class="form-group">
                        <div class="checkbox">
                            <label for="featured-checker">
                                {!! Form::checkbox('featured', $product->featured, $product->featured, array('id' => 'featured-checker')) !!} {{ Lang::get('redminportal::forms.featured') }}
                            </label>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="checkbox">
                            <label for="active-checker">
                                {!! Form::checkbox('active', $product->active, $product->active, array('id' => 'active-checker')) !!} {{ Lang::get('redminportal::forms.active') }}
                            </label>
                        </div>
                    </div>
                </div>
                {{-- Load Select Category partial form --}}
                @include('redminportal::partials.form-select-category', [
                    'select_category_selected_name' => 'category_id',
                    'select_category_selected_id' => $product->category_id,
                    'select_category_categories' => $categories,
                    'select_category_required_field' => true
                ])
		        <div>
                    <div class="fileupload fileupload-new" data-provides="fileupload">
                      <div class="fileupload-preview thumbnail" style="width: 200px; height: 150px;"></div>
                      <div>
                        <span class="btn btn-default btn-file"><span class="fileupload-new">{{ Lang::get('redminportal::forms.select_image') }}</span><span class="fileupload-exists">{{ Lang::get('redminportal::forms.change_image') }}</span>{!! Form::file('image') !!}</span>
                        <a href="#" class="btn btn-danger fileupload-exists" data-dismiss="fileupload">{{ Lang::get('redminportal::forms.remove_image') }}</a>
                      </div>
                    </div>
                </div>
                <div class="well visible-xs">
                    <div class='form-actions text-right'>
                        {!! Form::submit(Lang::get('redminportal::buttons.create'), array('class' => 'btn btn-primary btn-sm')) !!}
                    </div>
                </div>
	        </div>
        </div>
    {!! Form::close() !!}
    <!-- Modal confirmation window -->
    @include('redminportal::partials.modal-window', [
        'modal_id' => 'confirm-modal',
        'modal_title' => Lang::get('redminportal::messages.confirm_delete'),
        'modal_body' => Lang::get('redminportal::messages.are_you_sure_you_want_to_delete'),
        'modal_footer' => '<button type="button" class="btn btn-default" data-dismiss="modal">' . Lang::get('redminportal::buttons.delete_no') . '</button><a href="#" id="confirm-modal-proceed" class="btn btn-danger">' . Lang::get('redminportal::buttons.delete_yes') . '</a>'
    ])
    <!-- End of modal window -->
    <!-- if product exists (END) -->
    @endif
@stop

@section('footer')
    @parent
    <script src="{{ URL::to('vendor/redooor/redminportal/js/bootstrap-fileupload.js') }}"></script>
    <script>
        !function ($) {
            $(function(){
                $(document).on('click', '.btn-confirm', function(e) {
                    e.preventDefault();
                    $delete_url = $(this).attr('href');
                    $('#confirm-modal-proceed').attr('href', $delete_url);
                    $('#confirm-modal').modal('show');
                });
            })
        }(window.jQuery);
    </script>
    @include('redminportal::plugins/tinymce')
    @include('redminportal::plugins/tagsinput')
@stop
