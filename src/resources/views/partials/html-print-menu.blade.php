{{--
    Input form template for reuse
    --------------------------------
    Usage Example:
    --------------------------------
    @include('redminportal::partials.html-print-menu', [
        'menus' => 'Array of menus, e.g. see config.menu',
        'class' => 'optional class'
    ])
    --------------------------------
--}}
<ul
    @if(isset($class))
        class="{{ $class }}"
    @endif
>
@foreach ($menus as $menu)
    @if (! $menu['hide'])
        @if (! array_key_exists('path', $menu))
            <li class="nav-sub-header"><span>{{ \Lang::get('redminportal::menus.' . $menu['name']) }}</span>
        @elseif ($menu['path'] == '')
            <li class="nav-sub-header"><span>{{ \Lang::get('redminportal::menus.' . $menu['name']) }}</span>
        @else
            @if (\Request::is($menu['path']) or \Request::is($menu['path'] . '/*'))
                <li class="active">
            @else
                <li>
            @endif
            <a href="{{ \URL::to($menu['path']) }}">{{ \Lang::get('redminportal::menus.' . $menu['name']) }}</a>
        @endif
        @if (array_key_exists('children', $menu))
            @include('redminportal::partials.html-print-menu', ['menus' => $menu['children']])
        @endif
        </li>
    @endif
@endforeach
</ul>
