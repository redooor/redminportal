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
<form method="POST" action="{{ $action }}" accept-charset="UTF-8" role="form" class="d-flex flex-wrap align-items-center gap-2">
    <input type="hidden" name="_token" value="{{ csrf_token() }}">
        <div class="input-group w-auto">
            <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
        </div>
        <select class="form-select form-select-sm w-auto" name="field">
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
        <div class="input-group w-auto">
            <input class="form-control form-control-sm" placeholder="Search" title="Search" name="search" type="text" value="{{ $value }}">
            <a class="btn btn-secondary btn-sm" href="{{ $view }}"><i class="fa-solid fa-xmark"></i> {{ trans('redminportal::buttons.clear') }}</a>
        </div>
        @else
        <input class="form-control form-control-sm w-auto" placeholder="Search" title="Search" name="search" type="text">
        @endif
</form>