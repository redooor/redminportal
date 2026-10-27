@extends('redminportal::layouts.master')

@section('navbar-breadcrumb')
    <li class="nav-item"><a class="nav-link" href="{{ URL::to('admin/bundles') }}">{{ Lang::get('redminportal::menus.bundles') }}</a></li>
    <li class="nav-item"><span class="nav-link active">{{ Lang::get('redminportal::forms.edit') }}</span></li>
@stop

@section('content')
    
    @include('redminportal::partials.errors')

    {!! Form::open(array('files' => TRUE, 'action' => '\Redooor\Redminportal\App\Http\Controllers\BundleController@postStore', 'role' => 'form')) !!}
        {!! Form::hidden('id', $bundle->id) !!}

    	<div class='row'>
            <div class="col-lg-3 order-lg-last">
                <div class="card card-body bg-body-tertiary">
                    <div class='form-actions'>
                        {!! HTML::link('admin/bundles', Lang::get('redminportal::buttons.cancel'), array('class' => 'btn btn-link btn-sm'))!!}
                        {!! Form::submit(Lang::get('redminportal::buttons.save'), array('class' => 'btn btn-primary btn-sm float-end')) !!}
                    </div>
                </div>
                <div class='card card-body bg-body-tertiary p-2'>
                    <div class="mb-3">
                        <div class="form-check">
                            <label class="form-check-label" for="featured-checker">
                                {!! Form::checkbox('featured', $bundle->featured, $bundle->featured, array('class' => 'form-check-input', 'id' => 'featured-checker')) !!} {{ Lang::get('redminportal::forms.featured') }}
                            </label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <label class="form-check-label" for="active-checker">
                                {!! Form::checkbox('active', $bundle->active, $bundle->active, array('class' => 'form-check-input', 'id' => 'active-checker')) !!} {{ Lang::get('redminportal::forms.active') }}
                            </label>
                        </div>
                    </div>
                </div>
                {{-- Load Select Category partial form --}}
                @include('redminportal::partials.form-select-category', [
                    'select_category_selected_name' => 'category_id',
                    'select_category_selected_id' => $bundle->category_id,
                    'select_category_categories' => $categories
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
                        <h4 class="card-title mb-0">{{ Lang::get('redminportal::forms.edit_bundle') }}</h4>
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
                                    {!! Form::text('name', $bundle->name, array('class' => 'form-control')) !!}
                                </div>

                                <div class="mb-3">
                                    {!! Form::label('short_description', Lang::get('redminportal::forms.summary')) !!}
                                    {!! Form::text('short_description', $bundle->short_description, array('class' => 'form-control')) !!}
                                </div>

                                <div class="mb-3">
                                    {!! Form::label('long_description', Lang::get('redminportal::forms.description')) !!}
                                    {!! Form::textarea('long_description', $bundle->long_description, array('class' => 'form-control', 'style' => 'height:200px')) !!}
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
                        <h4 class="card-title mb-0">{{ Lang::get('redminportal::forms.bundle_properties') }}</h4>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            {!! Form::label('sku', Lang::get('redminportal::forms.sku')) !!}
                            {!! Form::text('sku', $bundle->sku, array('class' => 'form-control')) !!}
                        </div>
                        <div class="mb-3">
                            {!! Form::label('price', Lang::get('redminportal::forms.price')) !!}
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                {!! Form::text('price', $bundle->price, array('class' => 'form-control')) !!}
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
                        <h4 class="card-title mb-0">{{ Lang::get('redminportal::forms.bundle_items') }}</h4>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <h4>{{ Lang::get('redminportal::forms.products') }}</h4>
                            @if (count($products) > 0)
                            <p></p>
                            {!! Form::select('product_id', $products, $product_id, array('class' => 'form-select', 'id' => 'product_id', 'multiple', 'name' => 'product_id[]')) !!}
                            @else
                            <div class="alert alert-warning">{{ Lang::get('redminportal::messages.no_product_found') }}</div>
                            @endif
                        </div>
                        <div class="mb-3">
                            <h4>{{ Lang::get('redminportal::forms.membership_modules') }}</h4>
                            @if (count($membermodules) > 0)
                            <p></p>
                            {!! Form::select('pricelist_id', $membermodules, $pricelist_id, array('class' => 'form-select', 'id' => 'pricelist_id', 'multiple', 'name' => 'pricelist_id[]')) !!}
                            @else
                            <div class="alert alert-warning">{{ Lang::get('redminportal::messages.no_pricelist_found') }}</div>
                            @endif
                        </div>
                    </div>
                    <div class="card-footer">
                        <p class="form-text">{{ Lang::get('redminportal::messages.allow_select_multiple') }}</p>
                        <p class="form-text">{{ Lang::get('redminportal::messages.how_to_deselect_multiple') }}</p>
                    </div>
                </div>
                {!! Redminportal::html()->uploadedImages($bundle) !!}
            </div>
        </div>
    {!! Form::close() !!}
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
    @include('redminportal::plugins/editor', ['editorImages' => $bundle->images])
    @include('redminportal::plugins/tagsinput')
@stop
