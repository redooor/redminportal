{{--
    Uploaded image gallery with primary image and display-order controls
    --------------------------------
    Usage Example:
    --------------------------------
    @include('redminportal::partials.image-gallery', [
        'images' => $model->images,
        'imagine' => $imagine,
        'base' => 'admin/posts',
        'alt' => $model->title
    ])
    --------------------------------
--}}
@if (count($images) > 0)
@php
    $nonPrimaryImages = $images->filter(function ($image) {
        return ! $image->is_primary;
    })->values();
    $firstNonPrimaryId = $nonPrimaryImages->isNotEmpty() ? $nonPrimaryImages->first()->id : null;
    $lastNonPrimaryId = $nonPrimaryImages->isNotEmpty() ? $nonPrimaryImages->last()->id : null;
    $removeBase = isset($removeBase) && $removeBase ? $removeBase : $base . '/imgremove';
@endphp
<div class="card">
    <div class="card-header">
        <h4 class="card-title mb-0">{{ Lang::get('redminportal::forms.uploaded_photos') }}</h4>
    </div>
    <div class="card-body">
        <div class="row">
            @foreach ($images as $image)
            <div class="col-lg-3 redmin-gallery-item" data-image-id="{{ $image->id }}">
                <img src="{{ url($imagine->getUrl($image->path)) }}" class="img-thumbnail" alt="{{ $alt }}">
                @if ($image->is_primary)
                <div><span class="badge bg-primary redmin-gallery-primary">{{ Lang::get('redminportal::forms.primary_image') }}</span></div>
                @endif
                <br><br>
                <div class="btn-group btn-group-sm">
                    @if (! $image->is_primary)
                    @permitted($base . '/imgprimary/' . $image->id)
                    <a href="{{ URL::to($base . '/imgprimary/' . $image->id) }}" class="btn btn-warning" data-method="post" title="{{ Lang::get('redminportal::forms.make_primary') }}" aria-label="{{ Lang::get('redminportal::forms.make_primary') }}">
                        <i class="fa-solid fa-star"></i>
                    </a>
                    @endpermitted
                    @if ($image->id != $firstNonPrimaryId)
                    @permitted($base . '/imgmove/' . $image->id . '/earlier')
                    <a href="{{ URL::to($base . '/imgmove/' . $image->id . '/earlier') }}" class="btn btn-secondary" data-method="post" title="{{ Lang::get('redminportal::forms.move_image_earlier') }}" aria-label="{{ Lang::get('redminportal::forms.move_image_earlier') }}">
                        <i class="fa-solid fa-arrow-left"></i>
                    </a>
                    @endpermitted
                    @endif
                    @if ($image->id != $lastNonPrimaryId)
                    @permitted($base . '/imgmove/' . $image->id . '/later')
                    <a href="{{ URL::to($base . '/imgmove/' . $image->id . '/later') }}" class="btn btn-secondary" data-method="post" title="{{ Lang::get('redminportal::forms.move_image_later') }}" aria-label="{{ Lang::get('redminportal::forms.move_image_later') }}">
                        <i class="fa-solid fa-arrow-right"></i>
                    </a>
                    @endpermitted
                    @endif
                    @endif
                    @permitted($removeBase . '/' . $image->id)
                    <a href="{{ URL::to($removeBase . '/' . $image->id) }}" class="btn btn-danger btn-confirm" title="{{ Lang::get('redminportal::forms.remove_images') }}" aria-label="{{ Lang::get('redminportal::forms.remove_images') }}">
                        <i class="fa-solid fa-xmark"></i>
                    </a>
                    @endpermitted
                    <a href="{{ URL::to($imagine->getUrl($image->path, 'large')) }}" class="btn btn-primary btn-copy" title="{{ Lang::get('redminportal::forms.copy_link') }}" aria-label="{{ Lang::get('redminportal::forms.copy_link') }}">
                        <i class="fa-solid fa-link"></i>
                    </a>
                    <a href="{{ URL::to($imagine->getUrl($image->path, 'large')) }}" class="btn btn-info" target="_blank" data-lightbox="uploaded-images" data-lightbox-title="{{ $image->path }}" aria-label="{{ Lang::get('redminportal::buttons.view_image') }}">
                        <i class="fa-solid fa-eye"></i>
                    </a>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endif
@include('redminportal::partials.confirm-delete')
