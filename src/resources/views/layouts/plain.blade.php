<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>RedminPortal by Redooor</title>
        <link rel="stylesheet" href="{{ URL::to('vendor/redooor/redminportal/css/jquery-ui/themes/blitzer/jquery-ui.min.css') }}">
        <link rel="stylesheet" href="{{ URL::to('vendor/redooor/redminportal/css/bootstrap.min.css') }}">
        <link rel="stylesheet" href="{{ URL::to('vendor/redooor/redminportal/css/fontawesome.min.css') }}">
        <link rel="stylesheet" href="{{ URL::to('vendor/redooor/redminportal/css/redmaterials.min.css') }}">
        <link rel="stylesheet" href="{{ URL::to('vendor/redooor/redminportal/css/redminportal.min.css') }}">
        <link rel="shortcut icon" type="image/png" href="{{ URL::to('vendor/redooor/redminportal/img/favicon.png') }}"/>
        @section('head')
        @show
    </head>
    <body>
        <header id="header">
            <nav class="navbar navbar-expand-md navbar-light bg-body fixed-top">
                <div class="container-fluid">
                    <a href="{{ URL::to('admin') }}" class="navbar-brand">
                        <img src="{{ URL::to('vendor/redooor/redminportal/img/favicon.png') }}" title="RedminPortal" class="redooor-nav-logo"> RedminPortal
                    </a>
                    @if (Auth::guard('redminguard')->check())
                    <button type="button" class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#navbar-user" aria-controls="navbar-user" aria-expanded="false" aria-label="Toggle menu">
                        <i class="fa-solid fa-user"></i>
                    </button>
                    <div class="collapse navbar-collapse justify-content-end" id="navbar-user">
                        <ul class="navbar-nav">
                            <li class="nav-item"><a class="nav-link" href="{{ URL::to('logout') }}" data-method="post" title="{{ Lang::get('redminportal::menus.logout') }}"><i class="fa-solid fa-right-from-bracket"></i> {{ Lang::get('redminportal::menus.logout') }}</a></li>
                        </ul>
                    </div>
                    @endif
                </div>
            </nav>
        </header>

        <div id="main">
            <div class="container-fluid">
                <div class="col-md-12 main">
                    @yield('content')
                </div>
            </div>
        </div><!--End main-->

        @stack('modals')

        <script src="{{ URL::to('vendor/redooor/redminportal/js/jquery/jquery.min.js') }}"></script>
        <script src="{{ URL::to('vendor/redooor/redminportal/js/jquery-ui/jquery-ui.min.js') }}"></script>
        <script src="{{ URL::to('vendor/redooor/redminportal/js/bootstrap.bundle.min.js') }}"></script>
        <script src="{{ URL::to('vendor/redooor/redminportal/js/redmaterials.min.js') }}"></script>
        <script src="{{ URL::to('vendor/redooor/redminportal/js/redmin-post.min.js') }}" defer></script>
        @stack('scripts')

        @section('footer')
        @show
    </body>
</html>
