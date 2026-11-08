<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
        <title>RedminPortal by Redooor</title>
        <script>
            // Set the sidebar state before the stylesheets paint so a collapsed sidebar never flashes open.
            (function () {
                var open = true;

                try {
                    var desktop = !window.matchMedia || window.matchMedia('(min-width: 992px)').matches;

                    if (desktop) {
                        try {
                            if (window.localStorage.getItem('redmin.sidebar') === 'closed') {
                                open = false;
                            }
                        } catch (e) {
                            // Storage is unavailable (private mode): keep the desktop default.
                        }
                    } else {
                        open = false;
                    }
                } catch (e) {
                    // Assume a desktop viewport when matchMedia cannot be used.
                }

                if (open) {
                    document.documentElement.classList.add('sidebar-open');
                }

                document.addEventListener('DOMContentLoaded', function () {
                    var sidebar = document.getElementById('sidebar');
                    var isOpen = document.documentElement.classList.contains('sidebar-open');

                    if (!sidebar) {
                        return;
                    }
                    if (isOpen) {
                        sidebar.removeAttribute('inert');
                        sidebar.removeAttribute('aria-hidden');
                    } else {
                        sidebar.setAttribute('inert', '');
                        sidebar.setAttribute('aria-hidden', 'true');
                    }
                });
            })();
        </script>
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
                    <button type="button" id="sidebar-toggle" class="btn btn-link navbar-brand sidebar-toggle" aria-controls="sidebar" aria-expanded="false" aria-label="{{ Lang::get('redminportal::menus.sidebar_expand') }}" data-label-open="{{ Lang::get('redminportal::menus.sidebar_collapse') }}" data-label-closed="{{ Lang::get('redminportal::menus.sidebar_expand') }}">
                        <i class="fa-solid fa-angles-left" aria-hidden="true"></i>
                        <i class="fa-solid fa-angles-right" aria-hidden="true"></i>
                    </button>
                    <ul class="navbar-nav navbar-breadcrumb d-none d-md-flex me-auto">
                        <li class="nav-item"><a class="nav-link" href="{{ URL::to('admin/dashboard') }}">{{ Lang::get('redminportal::menus.dashboard') }}</a></li>
                        @section('navbar-breadcrumb')
                        @show
                    </ul>
                    <button type="button" class="navbar-toggler ms-auto" data-bs-toggle="collapse" data-bs-target="#navbar-user" aria-controls="navbar-user" aria-expanded="false" aria-label="Toggle user menu">
                        <i class="fa-solid fa-user"></i>
                    </button>
                    <div class="collapse navbar-collapse flex-grow-0" id="navbar-user">
                        <ul class="navbar-nav d-md-none">
                            <li class="nav-item"><span class="nav-link disabled">Signed in as {{ Auth::guard('redminguard')->user()->first_name }} {{ Auth::guard('redminguard')->user()->last_name }}</span></li>
                            <li class="nav-item"><a class="nav-link" href="{{ url('myaccount') }}"><i class="fa-solid fa-gear"></i> {{ Lang::get('redminportal::menus.my_account') }}</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ URL::to('logout') }}" title="{{ Lang::get('redminportal::menus.logout') }}"><i class="fa-solid fa-right-from-bracket"></i> {{ Lang::get('redminportal::menus.logout') }}</a></li>
                        </ul>
                        <ul class="navbar-nav d-none d-md-flex">
                            <li class="nav-item dropdown">
                                <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown" role="button" aria-expanded="false"><i class="fa-solid fa-user"></i>&nbsp;&nbsp; {{ Auth::guard('redminguard')->user()->first_name }} {{ Auth::guard('redminguard')->user()->last_name }}</a>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a class="dropdown-item" href="{{ url('myaccount') }}"><i class="fa-solid fa-gear"></i> {{ Lang::get('redminportal::menus.my_account') }}</a></li>
                                    <li><a class="dropdown-item" href="{{ URL::to('logout') }}" title="{{ Lang::get('redminportal::menus.logout') }}"><i class="fa-solid fa-right-from-bracket"></i> {{ Lang::get('redminportal::menus.logout') }}</a></li>
                                </ul>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>
        </header>

        <div id="main">
            <div class="container-fluid">
                <div id="sidebar-wrapper">
                    <div id="sidebar" class="shadow-depth-1" inert>
                        <div id="sidebar-title">
                            <a href="{{ URL::to('admin') }}" class="redooor-nav-logo"><img src="{{ URL::to('vendor/redooor/redminportal/img/favicon.png') }}" title="RedminPortal"> RedminPortal</a>
                            <button type="button" id="sidebar-close" class="btn-close btn-close-white sidebar-toggle d-lg-none" aria-label="{{ Lang::get('redminportal::menus.sidebar_collapse') }}"></button>
                        </div>
                        {!! Redminportal::html()->printMenu(config('redminportal::menu'), 'nav nav-sidebar') !!}
                    </div>
                    <div id="sidebar-overlay" class="sidebar-toggle"></div>
                    <div class="main-content">
                        @yield('content')
                    </div>
                </div>
            </div>
        </div><!--End main-->
        
        <!-- Modal confirmation window -->
        {!! Redminportal::html()->modalWindow(
            'confirm-modal',
            Lang::get('redminportal::messages.confirm_delete'),
            Lang::get('redminportal::messages.are_you_sure_you_want_to_delete'),
            '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">' . Lang::get('redminportal::buttons.delete_no') . '</button><a href="#" id="confirm-modal-proceed" class="btn btn-danger">' . Lang::get('redminportal::buttons.delete_yes') . '</a>'
        ) !!}
        <!-- End of modal window -->
        
        <script src="{{ URL::to('vendor/redooor/redminportal/js/jquery/jquery.min.js') }}"></script>
        <script src="{{ URL::to('vendor/redooor/redminportal/js/jquery-ui/jquery-ui.min.js') }}"></script>
        <script src="{{ URL::to('vendor/redooor/redminportal/js/bootstrap.bundle.min.js') }}"></script>
        <script src="{{ URL::to('vendor/redooor/redminportal/js/redmaterials.min.js') }}"></script>
        <script src="{{ URL::to('vendor/redooor/redminportal/js/redmin-sidebar.min.js') }}" defer></script>
        <script>
            !function ($) {
                $(function(){
                    $(document).on('click', '.btn-confirm', function(e) {
                        e.preventDefault();
                        $delete_url = $(this).attr('href');
                        $('#confirm-modal-proceed').attr('href', $delete_url);
                        bootstrap.Modal.getOrCreateInstance(document.getElementById('confirm-modal')).show();
                    });
                })
            }(window.jQuery);
        </script>

        @section('footer')
        @show
    </body>
</html>
