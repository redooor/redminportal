{{--
    Search form template for reuse
    --------------------------------
    Usage Example:
    --------------------------------
    @include('redminportal::partials.form-search-template', [
        'view' => 'View Url',
        'action' => 'Route to post',
        'fields' => 'Array of available fields',
        'selected' => 'Optional selected field option',
        'value' => 'Optional value for search'
    ])
    --------------------------------
--}}
<form method="POST" action="{{ $action }}" accept-charset="UTF-8" role="form" class="form-inline">
    {!! csrf_field() !!}
    <div class="form-group">
        <div class="input-group">
            <span class="input-group-addon"><span class="fa fa-search"></span></span>
        </div>
        <select class="form-control input-sm" name="field">
            @if ($fields)
                @foreach ($fields as $field => $field_name)
                <option value="{{ $field }}"
                    @if (isset($selected) and $selected == $field)
                        selected
                    @endif
                >{{ $field_name }}</option>
                @endforeach
            @endif
        </select>
        @if (isset($value))
        <div class="input-group">
            <input class="form-control input-sm" placeholder="Search" title="Search" name="search" type="text" value="{{ $value }}">
            <span class="input-group-btn">
                <a class="btn btn-default btn-sm" href="{{ $view }}"><span class="glyphicon glyphicon-remove"></span> {{ trans('redminportal::buttons.clear') }}</a>
            </span>
        </div>
        @else
        <input class="form-control input-sm" placeholder="Search" title="Search" name="search" type="text">
        @endif
    </div>
</form>