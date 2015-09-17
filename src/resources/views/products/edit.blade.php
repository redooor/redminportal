@extends('redminportal::layouts.master')

@section('navbar-breadcrumb')
    <li><a href="{{ URL::to('admin/products') }}">{{ Lang::get('redminportal::menus.products') }}</a></li>
    <li class="active"><span class="navbar-text">{{ Lang::get('redminportal::forms.edit') }}</span></li>
@stop

@section('content')
    
    @include('redminportal::partials.errors')

    {!! Form::open(array('files' => TRUE, 'action' => '\Redooor\Redminportal\App\Http\Controllers\ProductController@postStore', 'role' => 'form')) !!}
        {!! Form::hidden('id', $product->id) !!}

    	<div class='row'>
            <div class="col-md-3 col-md-push-9">
                <div class="well">
                    <div class='form-actions'>
                        {!! HTML::link('admin/products', Lang::get('redminportal::buttons.cancel'), array('class' => 'btn btn-link btn-sm'))!!}
                        {!! Form::submit(Lang::get('redminportal::buttons.save'), array('class' => 'btn btn-primary btn-sm pull-right')) !!}
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
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <div class="panel-title">{{ Lang::get('redminportal::forms.category') }}</div>
                    </div>
                    <div class="panel-body">
                        {!! Form::hidden('category_id', $product->category_id, array('id' => 'category_id')) !!}
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
                        <h4 class="panel-title">{{ Lang::get('redminportal::forms.edit_product') }}</h4>
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
                                    {!! Form::label('name', Lang::get('redminportal::forms.title')) !!}
                                    {!! Form::text('name', $product->name, array('class' => 'form-control')) !!}
                                </div>

                                <div class="form-group">
                                    {!! Form::label('short_description', Lang::get('redminportal::forms.summary')) !!}
                                    {!! Form::text('short_description', $product->short_description, array('class' => 'form-control')) !!}
                                </div>

                                <div class="form-group">
                                    {!! Form::label('long_description', Lang::get('redminportal::forms.description')) !!}
                                    {!! Form::textarea('long_description', $product->long_description, array('class' => 'form-control', 'style' => 'height:200px')) !!}
                                </div>
                            </div>
                            @foreach(\Config::get('redminportal::translation') as $translation)
                                @if($translation['lang'] != 'en')
                                <div class="tab-pane" id="lang-{{ $translation['lang'] }}">
                                    <div class="form-group">
                                        {!! Form::label($translation['lang'] . '_name', Lang::get('redminportal::forms.title')) !!}
                                        @if ($translated)
                                        {!! Form::text($translation['lang'] . '_name', (array_key_exists($translation['lang'], $translated) ? $translated[$translation['lang']]->name : ''), array('class' => 'form-control')) !!}
                                        @else
                                        {!! Form::text($translation['lang'] . '_name', null, array('class' => 'form-control')) !!}
                                        @endif
                                    </div>

                                    <div class="form-group">
                                        {!! Form::label($translation['lang'] . '_short_description', Lang::get('redminportal::forms.summary')) !!}
                                        @if ($translated)
                                        {!! Form::text($translation['lang'] . '_short_description', (array_key_exists($translation['lang'], $translated) ? $translated[$translation['lang']]->short_description : ''), array('class' => 'form-control')) !!}
                                        @else
                                        {!! Form::text($translation['lang'] . '_short_description', null, array('class' => 'form-control')) !!}
                                        @endif
                                    </div>

                                    <div class="form-group">
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
                        <h4 class="panel-title">{{ Lang::get('redminportal::forms.product_variations') }}</h4>
                    </div>
                    <div class="panel-progress">
                        <div class="progress">
                            <div id="variant-loading-progress" class="progress-bar progress-bar-info progress-bar-striped" role="progressbar" aria-valuenow="80" aria-valuemin="0" aria-valuemax="100" style="width: 0%">
                                <span class="sr-only">Loading</span>
                            </div>
                        </div>
                    </div>
                    <div id="list-variants" data-url="{{ url('admin/products/list-variants/' . $product->id) }}"></div>
                    <div class="panel-footer text-right">
                        <a id="add-product-variant" href="{{ url('admin/products/create-variant/' . $product->id) }}" class="btn btn-primary btn-sm">Add</a>
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
                            <div class='col-md-3'>
                                {!! HTML::image($imagine->getUrl($image->path), $product->name, array('class' => 'img-thumbnail', 'alt' => $image->path)) !!}
                                <br><br>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ URL::to('admin/products/imgremove/' . $image->id) }}" class="btn btn-danger btn-confirm">
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
        'modal_footer' => '<button type="button" class="btn btn-default" data-dismiss="modal">' . Lang::get('redminportal::buttons.delete_no') . '</button><a href="#" id="variant-remove-modal-proceed" class="btn btn-danger">' . Lang::get('redminportal::buttons.delete_yes') . '</a>'
    ])
    <!-- End of modal window -->
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
    @include('redminportal::plugins/tinymce')
@stop
