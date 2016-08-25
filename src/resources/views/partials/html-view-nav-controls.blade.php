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
    <div class="col-md-12">
        <div class="nav-controls text-right">
            <div class="btn-group" role="group">
            @if (count($models) > 0)
                <a href="" class="btn btn-default btn-sm disabled btn-text">{{ trans('redminportal::messages.list_from_to', ['firstItem' => $models->firstItem(), 'lastItem' => $models->lastItem(), 'totalItem' => $models->total()]) }}</a>
            @endif
            <a href="{{ url('admin/' . $model_view . '/create') }}" class="btn btn-primary btn-sm">{{ Lang::get('redminportal::buttons.create_new') }}</a>
        </div>
        </div>
    </div>
</div>
