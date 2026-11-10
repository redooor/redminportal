{{--
    Images form template for reuse
    --------------------------------
    Usage Example:
    --------------------------------
    @include('redminportal::partials.images', [
        'model' => 'Imageable object'
    ])
    --------------------------------
--}}
@if (count($model->images) > 0)
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">{{ Lang::get('redminportal::forms.uploaded_photos') }}</h5>
    </div>
    <div class="card-body">
        <div class="row">
            @foreach ($model->images as $image)
            <div class="col-lg-3">
                <img src="{{ url(Redminportal::imagine()->getUrl($image->path)) }}" class="img-thumbnail" alt="{{ $model->name }}">
                <br><br>
                <div class="btn-group btn-group-sm">
                    <a href="{{ URL::to('admin/images/delete/' . $image->id) }}" class="btn btn-danger btn-confirm">
                        <i class="fa-solid fa-xmark"></i>
                    </a>
                    <a href="{{ URL::to(Redminportal::imagine()->getUrl($image->path, 'large')) }}" class="btn btn-primary btn-copy">
                        <i class="fa-solid fa-link"></i>
                    </a>
                    <a href="{{ URL::to(Redminportal::imagine()->getUrl($image->path, 'large')) }}" class="btn btn-info" target="_blank" data-lightbox="uploaded-images" data-lightbox-title="{{ $image->path }}" aria-label="{{ Lang::get('redminportal::buttons.view_image') }}">
                        <i class="fa-solid fa-eye"></i>
                    </a>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endif