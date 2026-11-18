@extends('redminportal::layouts.master')

@section('navbar-breadcrumb')
    <li class="nav-item"><a class="nav-link" href="{{ URL::to('admin/bundles') }}">{{ Lang::get('redminportal::menus.bundles') }}</a></li>
    <li class="nav-item"><span class="nav-link active">{{ Lang::get('redminportal::forms.edit') }}</span></li>
@stop

@section('content')
    
    @include('redminportal::partials.errors')

    <form method="POST" action="{{ action('\Redooor\Redminportal\App\Http\Controllers\BundleController@postStore') }}" enctype="multipart/form-data" role="form">
    @csrf
        <input type="hidden" name="id" value="{{ old('id', $bundle->id) }}">

    	<div class='row'>
            <div class="col-lg-3 order-lg-last">
                <div class="card card-body bg-body-tertiary">
                    <div class='form-actions'>
                        <a href="{{ URL::to('admin/bundles') }}" class="btn btn-link btn-sm">{{ Lang::get('redminportal::buttons.cancel') }}</a>
                        <input type="submit" value="{{ Lang::get('redminportal::buttons.save') }}" class="btn btn-primary btn-sm float-end">
                    </div>
                </div>
                <div class='card card-body bg-body-tertiary p-2'>
                    <div class="mb-3">
                        <div class="form-check">
                            <label class="form-check-label" for="featured-checker">
                                <input type="checkbox" name="featured" value="1" id="featured-checker" class="form-check-input" @checked(session()->hasOldInput() ? old('featured') : $bundle->featured)> {{ Lang::get('redminportal::forms.featured') }}
                            </label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <label class="form-check-label" for="active-checker">
                                <input type="checkbox" name="active" value="1" id="active-checker" class="form-check-input" @checked(session()->hasOldInput() ? old('active') : $bundle->active)> {{ Lang::get('redminportal::forms.active') }}
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
                    @include('redminportal::partials.image-input')
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
                                    <label for="name">{{ Lang::get('redminportal::forms.title') }}</label>
                                    <input type="text" name="name" id="name" value="{{ old('name', $bundle->name) }}" class="form-control">
                                </div>

                                <div class="mb-3">
                                    <label for="short_description">{{ Lang::get('redminportal::forms.summary') }}</label>
                                    <input type="text" name="short_description" id="short_description" value="{{ old('short_description', $bundle->short_description) }}" class="form-control">
                                </div>

                                <div class="mb-3">
                                    <label for="long_description">{{ Lang::get('redminportal::forms.description') }}</label>
                                    <textarea name="long_description" id="long_description" class="form-control" style="height:200px">{{ old('long_description', $bundle->long_description) }}</textarea>
                                </div>
                            </div>
                            @foreach(\Config::get('redminportal::translation') as $translation)
                                @if($translation['lang'] != 'en')
                                <div class="tab-pane fade" id="lang-{{ $translation['lang'] }}">
                                    <div class="mb-3">
                                        <label for="{{ $translation['lang'] . '_name' }}">{{ Lang::get('redminportal::forms.title') }}</label>
                                        @if ($translated)
                                        <input type="text" name="{{ $translation['lang'] . '_name' }}" id="{{ $translation['lang'] . '_name' }}" value="{{ old($translation['lang'] . '_name', (array_key_exists($translation['lang'], $translated) ? $translated[$translation['lang']]->name : '')) }}" class="form-control">
                                        @else
                                        <input type="text" name="{{ $translation['lang'] . '_name' }}" id="{{ $translation['lang'] . '_name' }}" value="{{ old($translation['lang'] . '_name', null) }}" class="form-control">
                                        @endif
                                    </div>

                                    <div class="mb-3">
                                        <label for="{{ $translation['lang'] . '_short_description' }}">{{ Lang::get('redminportal::forms.summary') }}</label>
                                        @if ($translated)
                                        <input type="text" name="{{ $translation['lang'] . '_short_description' }}" id="{{ $translation['lang'] . '_short_description' }}" value="{{ old($translation['lang'] . '_short_description', (array_key_exists($translation['lang'], $translated) ? $translated[$translation['lang']]->short_description : '')) }}" class="form-control">
                                        @else
                                        <input type="text" name="{{ $translation['lang'] . '_short_description' }}" id="{{ $translation['lang'] . '_short_description' }}" value="{{ old($translation['lang'] . '_short_description', null) }}" class="form-control">
                                        @endif
                                    </div>

                                    <div class="mb-3">
                                        <label for="{{ $translation['lang'] . '_long_description' }}">{{ Lang::get('redminportal::forms.description') }}</label>
                                        @if ($translated)
                                        <textarea name="{{ $translation['lang'] . '_long_description' }}" id="{{ $translation['lang'] . '_long_description' }}" class="form-control" style="height:200px">{{ old($translation['lang'] . '_long_description', (array_key_exists($translation['lang'], $translated) ? $translated[$translation['lang']]->long_description : '')) }}</textarea>
                                        @else
                                        <textarea name="{{ $translation['lang'] . '_long_description' }}" id="{{ $translation['lang'] . '_long_description' }}" class="form-control" style="height:200px">{{ old($translation['lang'] . '_long_description', null) }}</textarea>
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
                            <label for="sku">{{ Lang::get('redminportal::forms.sku') }}</label>
                            <input type="text" name="sku" id="sku" value="{{ old('sku', $bundle->sku) }}" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label for="price">{{ Lang::get('redminportal::forms.price') }}</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="text" name="price" id="price" value="{{ old('price', $bundle->price) }}" class="form-control">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="tags">{{ Lang::get('redminportal::forms.tags_separated_by_comma') }}</label>
                            <input type="text" name="tags" id="tags" value="{{ old('tags', $tagString) }}" class="form-control">
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
                            <select name="product_id[]" id="product_id" class="form-select" multiple>
                                @foreach ($products as $k => $label)
                                <option value="{{ $k }}" @selected(in_array((string) $k, array_map('strval', (array) old('product_id', $product_id)), true))>{{ $label }}</option>
                                @endforeach
                            </select>
                            @else
                            <div class="alert alert-warning">{{ Lang::get('redminportal::messages.no_product_found') }}</div>
                            @endif
                        </div>
                        <div class="mb-3">
                            <h4>{{ Lang::get('redminportal::forms.membership_modules') }}</h4>
                            @if (count($membermodules) > 0)
                            <p></p>
                            <select name="pricelist_id[]" id="pricelist_id" class="form-select" multiple>
                                @foreach ($membermodules as $k => $label)
                                <option value="{{ $k }}" @selected(in_array((string) $k, array_map('strval', (array) old('pricelist_id', $pricelist_id)), true))>{{ $label }}</option>
                                @endforeach
                            </select>
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
                @include('redminportal::partials.image-gallery', [
                    'images' => $bundle->images,
                    'imagine' => $imagine,
                    'base' => 'admin/bundles',
                    'alt' => $bundle->name,
                    'removeBase' => 'admin/images/delete',
                ])
            </div>
        </div>
    </form>
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
