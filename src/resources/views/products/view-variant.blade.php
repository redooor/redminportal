@extends('redminportal::layouts.bare')

@section('content')
    @include('redminportal::partials.errors')

    @if (isset($product))
    <div class='row'>
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">{{ Lang::get('redminportal::forms.view_product') }}</h4>
                </div>
                <div class="card-body">
                    @include('redminportal::partials.lang-selector-view', [
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
                        <div>{{ $product->sku }}</div>
                    </div>
                    <div class="mb-3">
                        {!! Form::label('price', Lang::get('redminportal::forms.price')) !!}
                        <div>$ {{ $product->price }}</div>
                    </div>
                    <div class="mb-3">
                        {!! Form::label('tags', Lang::get('redminportal::forms.tags_separated_by_comma')) !!}
                        <div class="clearfix">
                            @foreach ($product->tags as $tag)
                            <span class="badge text-bg-info" style="font-size:1em;">{{ $tag->name }}</span>
                            @endforeach
                        </div>
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
                        @if ($product->weight)
                        <div class="mb-3">{{ $product->weight }} {{ $product->weight_unit }}</div>
                        @else
                        <div class="mb-3">{{ Lang::get('redminportal::messages.no_weight_information_found') }}</div>
                        @endif
                    </div>
                    <!-- Volume information -->
                    <div class="mb-3">
                        <label>{{ Lang::get('redminportal::forms.volume') }}</label>
                        @if ($product->length || $product->width || $product->height)
                        <div class="mb-3">(L) {{ $product->length }} {{ $product->volume_unit }}, (W) {{ $product->width }} {{ $product->volume_unit }}, (H) {{ $product->height }} {{ $product->volume_unit }}</div>
                        @else
                        <div class="mb-3">{{ Lang::get('redminportal::messages.no_volume_information_found') }}</div>
                        @endif
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
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </div>
        <div class="col-md-4">
            <div class='card card-body bg-body-tertiary p-2'>
                <div class="mb-3">
                    <div class="form-check">
                        <label class="form-check-label" for="featured-checker">
                            {!! Form::checkbox('featured', $product->featured, $product->featured, array('class' => 'form-check-input', 'id' => 'featured-checker', 'disabled' => 'disabled')) !!} {{ Lang::get('redminportal::forms.featured') }}
                        </label>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="form-check">
                        <label class="form-check-label" for="active-checker">
                            {!! Form::checkbox('active', $product->active, $product->active, array('class' => 'form-check-input', 'id' => 'active-checker', 'disabled' => 'disabled')) !!} {{ Lang::get('redminportal::forms.active') }}
                        </label>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-header">
                    <div class="card-title mb-0">{{ Lang::get('redminportal::forms.category') }}</div>
                </div>
                <div class="card-body">
                    <span class="badge text-bg-info" style="font-size:1em;">{{ $product->category->name }}</span>
                </div>
            </div>
        </div>
    </div>
    @endif
@stop
