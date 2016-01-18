{{--
    Input form template for reuse
    --------------------------------
    Usage Example:
    --------------------------------
    @include('redminportal::partials.table-sort-header', [
        'wrapper_classes' => 'optional classes',
        'url' => 'URL for sorting',
        'field_name' => 'Name of this field',
        'display_name' => 'Display name',
        'sortBy' => 'Sort By',
        'orderBy' => 'Order By'
    ])
    --------------------------------
--}}
<a class="block-header {{ $wrapper_classes or '' }}" href="{{ $url }}">
    {{ $display_name }}
    @if ($sortBy == $field_name)
    {!! ($orderBy != 'asc' ? '<span class="caret"></span>' : '<span class="dropup"><span class="caret"></span></span>') !!}
    @endif
</a>
