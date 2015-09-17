@if (isset($variants) and count($variants) > 0)
<ul class="list-group">
    @foreach ($variants as $variant)
    <li class="list-group-item">
        <div class="media">
            <div class="media-left">
                <a href="#">
                    @if ($variant->images->first())
                    <img class="media-object media-thumbnail" src="{{ url($imagine->getUrl($variant->images->first()->path)) }}" alt="{{ $variant->name }}">
                    @else
                    <img class="media-object media-thumbnail" src="{{ url('vendor/redooor/redminportal/img/no_image_available.png') }}" alt="{{ $variant->name }}">
                    @endif
                </a>
            </div>
            <div class="media-body">
                <div class="action-buttons pull-right">
                    <a class="btn btn-grey btn-sm edit-product-variant" href="{{ url('admin/products/edit-variant/' . $variantParent->id . '/' . $variant->id) }}">Edit</a>
                    <a class="btn btn-danger btn-sm delete-product-variant" href="{{ url('admin/products/delete-variant-json/' . $variant->id) }}">Delete</a>
                </div>
                <h4 class="media-heading"><a class="view-product-variant" href="{{ url('admin/products/view-variant/' . $variant->id) }}">{{ $variant->name }}</a></h4>
                <p>Price: <strong>{{ $variant->price }}</strong></p>
                <p>{{ $variant->short_description }}</p>
            </div>
        </div>
    </li>
    @endforeach
</ul>
@else
<div class="panel-body">
    {{ Lang::get('redminportal::messages.no_variant_found') }}
</div>
@endif
