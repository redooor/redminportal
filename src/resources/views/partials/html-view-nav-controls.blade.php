{{--
    Html template for reuse
    --------------------------------
    Usage Example:
    --------------------------------
    @include('redminportal::partials.html-view-nav-controls', [
        'models' => 'Array of models',
        'model_view' => 'Route to model view'
    ])
    --------------------------------
--}}
<div class="row">
    <div class="col-lg-12">
        <div class="nav-controls text-end">
            <div class="btn-group" role="group">
            @if (count($models) > 0)
                <a href="" class="btn btn-secondary btn-sm disabled btn-text">{{ trans('redminportal::messages.list_from_to', ['firstItem' => $models->firstItem(), 'lastItem' => $models->lastItem(), 'totalItem' => $models->total()]) }}</a>
            @endif
            @permitted('admin/' . $model_view . '/create')
            <a href="{{ url('admin/' . $model_view . '/create') }}" class="btn btn-primary btn-sm">{{ Lang::get('redminportal::buttons.create_new') }}</a>
            @endpermitted
        </div>
        </div>
    </div>
</div>
