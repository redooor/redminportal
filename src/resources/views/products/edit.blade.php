@extends('redminportal::layouts.master')

@section('navbar-breadcrumb')
    <li class="nav-item"><a class="nav-link" href="{{ URL::to('admin/products') }}">{{ Lang::get('redminportal::menus.products') }}</a></li>
    <li class="nav-item"><span class="nav-link active">{{ Lang::get('redminportal::forms.edit') }}</span></li>
@stop

@section('content')
    
    @include('redminportal::partials.errors')

    {!! Form::open(array('files' => TRUE, 'action' => '\Redooor\Redminportal\App\Http\Controllers\ProductController@postStore', 'role' => 'form')) !!}
        {!! Form::hidden('id', $product->id) !!}

    	<div class='row'>
            <div class="col-lg-3 order-lg-last">
                <div class="card card-body bg-body-tertiary">
                    <div class='form-actions'>
                        {!! HTML::link('admin/products', Lang::get('redminportal::buttons.cancel'), array('class' => 'btn btn-link btn-sm'))!!}
                        {!! Form::submit(Lang::get('redminportal::buttons.save'), array('class' => 'btn btn-primary btn-sm float-end')) !!}
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
                {{-- Load Select Category partial form --}}
                @include('redminportal::partials.form-select-category', [
                    'select_category_selected_name' => 'category_id',
                    'select_category_selected_id' => $product->category_id,
                    'select_category_categories' => $categories,
                    'select_category_required_field' => true
                ])
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
                        <h4 class="card-title mb-0">{{ Lang::get('redminportal::forms.edit_product') }}</h4>
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
                                    {!! Form::label('name', Lang::get('redminportal::forms.title')) !!}
                                    {!! Form::text('name', $product->name, array('class' => 'form-control')) !!}
                                </div>

                                <div class="mb-3">
                                    {!! Form::label('short_description', Lang::get('redminportal::forms.summary')) !!}
                                    {!! Form::text('short_description', $product->short_description, array('class' => 'form-control')) !!}
                                </div>

                                <div class="mb-3">
                                    {!! Form::label('long_description', Lang::get('redminportal::forms.description')) !!}
                                    {!! Form::textarea('long_description', $product->long_description, array('class' => 'form-control', 'style' => 'height:200px')) !!}
                                </div>
                            </div>
                            @foreach(\Config::get('redminportal::translation') as $translation)
                                @if($translation['lang'] != 'en')
                                <div class="tab-pane fade" id="lang-{{ $translation['lang'] }}">
                                    <div class="mb-3">
                                        {!! Form::label($translation['lang'] . '_name', Lang::get('redminportal::forms.title')) !!}
                                        @if ($translated)
                                        {!! Form::text($translation['lang'] . '_name', (array_key_exists($translation['lang'], $translated) ? $translated[$translation['lang']]->name : ''), array('class' => 'form-control')) !!}
                                        @else
                                        {!! Form::text($translation['lang'] . '_name', null, array('class' => 'form-control')) !!}
                                        @endif
                                    </div>

                                    <div class="mb-3">
                                        {!! Form::label($translation['lang'] . '_short_description', Lang::get('redminportal::forms.summary')) !!}
                                        @if ($translated)
                                        {!! Form::text($translation['lang'] . '_short_description', (array_key_exists($translation['lang'], $translated) ? $translated[$translation['lang']]->short_description : ''), array('class' => 'form-control')) !!}
                                        @else
                                        {!! Form::text($translation['lang'] . '_short_description', null, array('class' => 'form-control')) !!}
                                        @endif
                                    </div>

                                    <div class="mb-3">
                                        {!! Form::label($translation['lang'] . '_long_description', Lang::get('redminportal::forms.description')) !!}
                                        @if ($translated)
                                        {!! Form::textarea($translation['lang'] . '_long_description', (array_key_exists($translation['lang'], $translated) ? $translated[$translation['lang']]->long_description : ''), array('class' => 'form-control', 'style' => 'height:200px')) !!}
                                        @else
                                        {!! Form::textarea($translation['lang'] . '_long_description', null, array('class' => 'form-control', 'style' => 'height:200px')) !!}
                                        @endif
                                    </div>
                                </div>
                                @endif
                            @endforeach
                        </div>
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
                                @include('redminportal::partials.form-input', [
                                    'label' => Lang::get('redminportal::forms.weight'),
                                    'label_classes' => 'visually-hidden',
                                    'input_name' => 'weight',
                                    'input_options' => ['type' => 'number', 'step' => '0.001', 'placeholder' => '0.00'],
                                    'input_value' => $product->weight
                                ])
                                @include('redminportal::partials.form-select-option', [
                                    'label' => Lang::get('redminportal::forms.weight_unit'),
                                    'label_classes' => 'visually-hidden',
                                    'select_name' => 'weight_unit',
                                    'select_options' => $weight_units,
                                    'value_as_key' => true,
                                    'selected' => $product->weight_unit
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
                                    'help_text' => Lang::get('redminportal::forms.length'),
                                    'input_value' => $product->length
                                ])
                                @include('redminportal::partials.form-input', [
                                    'label' => Lang::get('redminportal::forms.width'),
                                    'label_classes' => 'visually-hidden',
                                    'input_name' => 'width',
                                    'input_options' => ['type' => 'number', 'step' => '0.001', 'placeholder' => '0.00'],
                                    'help_text' => Lang::get('redminportal::forms.width'),
                                    'input_value' => $product->width
                                ])
                                @include('redminportal::partials.form-input', [
                                    'label' => Lang::get('redminportal::forms.height'),
                                    'label_classes' => 'visually-hidden',
                                    'input_name' => 'height',
                                    'input_options' => ['type' => 'number', 'step' => '0.001', 'placeholder' => '0.00'],
                                    'help_text' => Lang::get('redminportal::forms.height'),
                                    'input_value' => $product->height
                                ])
                                @include('redminportal::partials.form-select-option', [
                                    'label' => Lang::get('redminportal::forms.volume_unit'),
                                    'label_classes' => 'visually-hidden',
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
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0">{{ Lang::get('redminportal::forms.product_variations') }}</h4>
                    </div>
                    <div class="card-progress">
                        <div class="progress">
                            <div id="variant-loading-progress" class="progress-bar bg-info progress-bar-striped" role="progressbar" aria-valuenow="80" aria-valuemin="0" aria-valuemax="100" style="width: 0%">
                                <span class="visually-hidden">Loading</span>
                            </div>
                        </div>
                    </div>
                    <div id="list-variants" data-url="{{ url('admin/products/list-variants/' . $product->id) }}"></div>
                    <div class="card-footer text-end">
                        <a id="add-product-variant" href="{{ url('admin/products/create-variant/' . $product->id) }}" class="btn btn-primary btn-sm">Add</a>
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
                            <div class='col-lg-3'>
                                {!! HTML::image($imagine->getUrl($image->path), $product->name, array('class' => 'img-thumbnail', 'alt' => $image->path)) !!}
                                <br><br>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ URL::to('admin/products/imgremove/' . $image->id) }}" class="btn btn-danger btn-confirm">
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
    
    <!-- Modal window iframe -->
    @include('redminportal::partials.modal-window', [
        'modal_id' => 'product-variant-modal',
        'modal_size' => 'modal-lg',
        'modal_progress' => 'modal-progress',
        'modal_title' => Lang::get('redminportal::forms.product_variation'),
        'modal_body' => '<iframe id="iframe-variant-form"></iframe>'
    ])
    <!-- End of modal window iframe -->

    <!-- Modal confirmation window -->
    @include('redminportal::partials.modal-window', [
        'modal_id' => 'variant-remove-modal',
        'modal_title' => Lang::get('redminportal::messages.confirm_delete'),
        'modal_body' => Lang::get('redminportal::messages.are_you_sure_you_want_to_delete'),
        'modal_footer' => '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">' . Lang::get('redminportal::buttons.delete_no') . '</button><a href="#" id="variant-remove-modal-proceed" class="btn btn-danger">' . Lang::get('redminportal::buttons.delete_yes') . '</a>'
    ])
    <!-- End of modal window -->
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
    <!-- This portion handles product variants -->
    <script>
        !function ($) {
            $(function() {
                //---------------------------------------------------------
                // On load, list the product variants
                //---------------------------------------------------------
                loadVariants();
                function loadVariants() {
                    $list_variant_url = $('#list-variants').attr('data-url');
                    $('#variant-loading-progress').css('width', '0%').parent().fadeIn(function() {
                        for(ipercent = 0; ipercent<=50; ipercent++) {
                            $('#variant-loading-progress').delay(4000).css('width', ipercent*2 + '%');
                        }
                    });
                    $('#list-variants').empty().load($list_variant_url, function() {
                        $('#variant-loading-progress').parent().fadeOut();
                    });
                }
                //---------------------------------------------------------
                // Add or View a product variant
                //---------------------------------------------------------
                $(document).on('click', '#add-product-variant, .view-product-variant, .edit-product-variant', function(e) {
                    e.preventDefault();
                    // Make iframe height 70% of window height
                    $window_height = $(window).height();
                    $('#iframe-variant-form').css('height', Math.round(($window_height*0.7-80), 0));
                    // Load the source
                    $create_url = $(this).attr('href');
                    $('#iframe-variant-form').removeAttr('src').empty().attr('src', $create_url).load(function() {
                        $('#modal-progress').parent().fadeOut();
                    });
                    $('#product-variant-modal').modal('show');
                });
                $('#product-variant-modal').on('shown.bs.modal', function (e) {
                    $('#modal-progress').css('width', '0%').parent().fadeIn(function() {
                        for(ipercent = 0; ipercent<=50; ipercent++) {
                            $('#modal-progress').delay(4000).css('width', ipercent*2 + '%');
                        }
                    });
                });
                // Clear content when modal hidden
                $('#product-variant-modal').on('hidden.bs.modal', function (e) {
                    $('#iframe-variant-form').removeAttr('src').empty();
                    loadVariants(); // reload list of product variants
                });
                //---------------------------------------------------------
                // Delete variant, using variant-remove-modal from master layout
                //---------------------------------------------------------
                $modal_body = $('#variant-remove-modal .modal-body').html();
                $(document).on('click', '.delete-product-variant', function(e) {
                    e.preventDefault();
                    $delete_url = $(this).attr('href');
                    $('#variant-remove-modal-proceed').attr('href', $delete_url);
                    $('#variant-remove-modal').modal('show');
                });
                $(document).on('click', '#variant-remove-modal-proceed', function(e) {
                    e.preventDefault();
                    $delete_url = $(this).attr('href');
                    $(this).addClass('disabled');
                    $.getJSON( $delete_url )
                        .done(function( json ) {
                            if (json.status == true) {
                                $('#variant-remove-modal').modal('hide');
                            } else {
                                $errormsg = '<div class="alert alert-danger">';
                                $errormsg += json.message;
                                $errormsg += '</div>';
                                $('#variant-remove-modal .modal-footer').hide();
                                $('#variant-remove-modal .modal-body').html($errormsg);
                            }
                        })
                        .fail(function( jqxhr, textStatus, error ) {
                            var err = textStatus + ", " + error;
                            $errormsg = '<div class="alert alert-danger">';
                            $errormsg += err;
                            $errormsg += '</div>';
                            $('#variant-remove-modal .modal-footer').hide();
                            $('#variant-remove-modal .modal-body').html($errormsg);
                        });
                });
                // Clear content when modal hidden
                $('#variant-remove-modal').on('hidden.bs.modal', function (e) {
                    $('#variant-remove-modal .modal-body').html($modal_body);
                    $('#variant-remove-modal .modal-footer').show();
                    $('#variant-remove-modal-proceed').removeClass('disabled');
                    loadVariants(); // reload list of product variants
                });
            });
        }(window.jQuery);
    </script>
    @include('redminportal::plugins/editor', ['editorImages' => $product->images])
    @include('redminportal::plugins/tagsinput')
@stop
