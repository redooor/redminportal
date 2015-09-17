@extends('redminportal::layouts.bare')

@section('content')
    @include('redminportal::partials.errors')

    @if (isset($product))
    <div class='row'>
        <div class="col-sm-8">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4 class="panel-title">{{ Lang::get('redminportal::forms.view_product') }}</h4>
                </div>
                <div class="panel-body">
                    @include('redminportal::partials.lang-selector-view', [
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
                        <div>{{ $product->sku }}</div>
                    </div>
                    <div class="form-group">
                        {!! Form::label('price', Lang::get('redminportal::forms.price')) !!}
                        <div>$ {{ $product->price }}</div>
                    </div>
                    <div class="form-group">
                        {!! Form::label('tags', Lang::get('redminportal::forms.tags_separated_by_comma')) !!}
                        <div class="clearfix">
                            @foreach ($product->tags as $tag)
                            <span class="label label-info" style="font-size:1em;">{{ $tag->name }}</span>
                            @endforeach
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
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </div>
        <div class="col-sm-4">
            <div class='well well-small'>
                <div class="form-group">
                    <div class="checkbox">
                        <label for="featured-checker">
                            {!! Form::checkbox('featured', $product->featured, $product->featured, array('id' => 'featured-checker', 'disabled' => 'disabled')) !!} {{ Lang::get('redminportal::forms.featured') }}
                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <div class="checkbox">
                        <label for="active-checker">
                            {!! Form::checkbox('active', $product->active, $product->active, array('id' => 'active-checker', 'disabled' => 'disabled')) !!} {{ Lang::get('redminportal::forms.active') }}
                        </label>
                    </div>
                </div>
            </div>
            <div class="panel panel-default">
                <div class="panel-heading">
                    <div class="panel-title">{{ Lang::get('redminportal::forms.category') }}</div>
                </div>
                <div class="panel-body">
                    <span class="label label-info" style="font-size:1em;">{{ $product->category->name }}</span>
                </div>
            </div>
        </div>
    </div>
    @endif
@stop
