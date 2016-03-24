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
<div class="panel panel-default">
    <div class="panel-heading">
        <h4 class="panel-title">{{ Lang::get('redminportal::forms.uploaded_photos') }}</h4>
    </div>
    <div class="panel-body">
        <div class="row">
            @foreach ($model->images as $image)
            <div class="col-md-3">
                <img src="{{ url(Redminportal::imagine()->getUrl($image->path)) }}" class="img-thumbnail" alt="{{ $model->name }}">
                <br><br>
                <div class="btn-group btn-group-sm">
                    <a href="{{ URL::to('admin/images/delete/' . $image->id) }}" class="btn btn-danger btn-confirm">
                        <span class="glyphicon glyphicon-remove"></span>
                    </a>
                    <a href="{{ URL::to(Redminportal::imagine()->getUrl($image->path, 'large')) }}" class="btn btn-primary btn-copy">
                        <span class="glyphicon glyphicon-link"></span>
                    </a>
                    <a href="{{ URL::to(Redminportal::imagine()->getUrl($image->path, 'large')) }}" class="btn btn-info" target="_blank">
                        <span class="glyphicon glyphicon-eye-open"></span>
                    </a>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endif