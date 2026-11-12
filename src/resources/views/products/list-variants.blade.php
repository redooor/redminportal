@if (isset($variants) and count($variants) > 0)
<ul class="list-group">
    @foreach ($variants as $variant)
    <li class="list-group-item">
        <div class="d-flex">
            <div class="flex-shrink-0 me-3">
                <a href="#">
                    @if ($variant->images->first())
                    <img class="media-thumbnail" src="{{ url($imagine->getUrl($variant->images->first()->path)) }}" alt="{{ $variant->name }}">
                    @else
                    <img class="media-thumbnail" src="{{ url('vendor/redooor/redminportal/img/no_image_available.png') }}" alt="{{ $variant->name }}">
                    @endif
                </a>
            </div>
            <div class="flex-grow-1">
                <div class="action-buttons float-end">
                    @permitted('admin/products/edit-variant/' . $variantParent->id . '/' . $variant->id)
                    <a class="btn btn-secondary btn-sm edit-product-variant" href="{{ url('admin/products/edit-variant/' . $variantParent->id . '/' . $variant->id) }}">Edit</a>
                    @endpermitted
                    @permitted('admin/products/delete-variant-json/' . $variant->id)
                    <a class="btn btn-danger btn-sm delete-product-variant" href="{{ url('admin/products/delete-variant-json/' . $variant->id) }}" data-ajax-post>Delete</a>
                    @endpermitted
                </div>
                <h4 class="mt-0"><a class="view-product-variant" href="{{ url('admin/products/view-variant/' . $variant->id) }}">{{ $variant->name }}</a></h4>
                <p>Price: <strong>{{ $variant->price }}</strong></p>
                <p>{{ $variant->short_description }}</p>
            </div>
        </div>
    </li>
    @endforeach
</ul>
@else
<div class="card-body">
    {{ Lang::get('redminportal::messages.no_variant_found') }}
</div>
@endif
